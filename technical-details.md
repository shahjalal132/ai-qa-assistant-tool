Here is the technical blueprint, database schema, and scaling strategy for your high-performance AI QA Tool.

---

### 1. Database Design (The "Mapping" Core)

To make the system flexible (different prompts for different URLs), we need a many-to-many relationship structure.

#### `prompts`
*   `id`, `title`, `system_instruction` (Text), `response_schema` (JSON - *defines the expected JSON keys from Gemini*), `is_active`.

#### `report_batches` (Optional but recommended)
*   `id`, `filename`, `user_id`, `created_at`.
*   *Why:* To group CSV uploads so you can run a prompt against a whole file at once.

#### `report_urls`
*   `id`, `batch_id`, `english_url`, `welsh_url`, `metadata` (JSON for any extra CSV columns), `status` (pending, processing, completed, failed).

#### `qa_executions` (The "Manager" Logic)
*   `id`, `prompt_id`, `report_url_id`, `status`, `error_message`, `started_at`, `completed_at`.
*   *Why:* This allows you to run **multiple different prompts** against the **same URL** without duplicating the URL record.

#### `results`
*   `id`, `execution_id`, `data` (JSONB - *stores the AI response*).

---

### 2. The AI Implementation Logic (Gemini 1.5 Flash)

To ensure different prompts generate different columns correctly, you must use **Gemini’s Structured Output (JSON Schema).**

**The Service Pattern (`App\Services\GeminiService`):**
When dispatching the job, pass the `response_schema` stored in your `prompts` table to Gemini.

```php
// Example of how to call Gemini with a dynamic schema
public function analyze($prompt, $enContent, $cyContent, $schema) {
    return Http::withHeaders(['x-goog-api-key' => $this->apiKey])
        ->post($this->baseUrl, [
            'contents' => [
                ['parts' => [['text' => "EN: $enContent \n CY: $cyContent \n Instruction: {$prompt->system_instruction} "]]]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'response_schema' => $schema // Pass the JSON schema from your prompts table here
            ]
        ]);
}
```

---

### 3. Performance & Scaling Strategy

#### A. Content Pre-Fetching (The Speed King)
Do not let Gemini fetch URLs.
1.  In your Laravel Job, use `Http::get($url)`.
2.  Use a simple regex or `strip_tags()` to remove `<script>`, `<style>`, and `<nav>`.
3.  **Why:** This reduces the token count by ~70% and cuts latency by ~10 seconds per request.

#### B. Queue Worker Management (Laravel Horizon)
Since you are worried about API exhaustion:
1.  **Use Redis:** It’s mandatory for high-concurrency Laravel queues.
2.  **Rate Limiting:** Use Laravel’s `Job Middleware` to limit Gemini API calls.
    ```php
    // In your Job file
    public function middleware()
    {
        return [(new RateLimited('gemini-api'))->allow(15)->every(60)]; 
    }
    ```
3.  **Scaling:** Set up **Laravel Horizon** with multiple supervisors. You can have 20 workers processing 20 URLs at once. If Gemini hits a 429 (Rate Limit), Horizon will back off and retry automatically.

---

### 4. Handling Dynamic CSV Exports

Since "Prompt A" might have 5 columns and "Prompt B" might have 10, your centralized view needs to be dynamic.

1.  **Storage:** The `results.data` column should be a `JSONB` type.
2.  **Display:** In your Vue/Blade/Livewire table, loop through the keys of the JSON:
    ```php
    @foreach($result->data as $column => $value)
        <td>{{ is_array($value) ? $value['reason'] : $value }}</td>
    @endforeach
    ```
3.  **Export:** When exporting to CSV, use the first result's JSON keys as the header row.

---

### 5. Error Handling & Resiliency

*   **Failed Jobs Table:** If a URL is 404 or Gemini times out, Laravel will move it to `failed_jobs`. You can build a "Retry" button in your UI.
*   **Timeout Management:** Set your `public $timeout = 120;` in the Job class, as AI reasoning can occasionally take time.
*   **Validation:** Before sending to AI, validate that the fetched HTML isn't empty. If the site has a firewall blocking your server, mark it as `failed` with a "Bot Blocked" reason immediately.

---

### Summary of the "Action Plan"

1.  **Migration:** Create the tables above (prioritizing the `qa_executions` table as the link).
2.  **Prompt CRUD:** Ensure you include a field for "Expected JSON Schema" so Gemini knows which columns to create.
3.  **The Job:** Create a `ProcessQA` job that:
    *   Fetches HTML for English/Welsh.
    *   Cleans it.
    *   Sends to Gemini 1.5 Flash.
    *   Saves JSON to the `results` table.
4.  **The Manager UI:** A simple page where you select a `Batch` + a `Prompt` and click "Run." This will bulk-insert records into `qa_executions` and dispatch the jobs.