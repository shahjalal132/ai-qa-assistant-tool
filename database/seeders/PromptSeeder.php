<?php

namespace Database\Seeders;

use App\Models\Prompt;
use Illuminate\Database\Seeder;

class PromptSeeder extends Seeder
{
    public function run(): void
    {
        $instruction = <<<'PROMPT'
You are a STRICT NHS QA auditor.

Compare these pages:

English: ${english}
Welsh: ${welsh}

IMPORTANT RULES:
- DO NOT compare file sizes
- Ignore schema metadata creators
- Ignore URLs containing https://beta-icc.gig.cymru/news/
- Authors block:
  - OK if missing on BOTH pages
  - FAIL only if present AND different

CHECK:

1. Content match (same meaning)
- the same content is on both pages but of different language
- identify any duplications, inconsistencies or potential issues
2. H1 matches URL slug
3. Formatting matches - e.g text, tables, styling etc.
4. Authors match (only if both present)
5. nhsuk-tag content matches

6. Report downloads:
- ONLY check if section exists
- If present:
  - filename must match H1
  - file must open successfully
  - Welsh file must contain Welsh language
- If not present → PASS

7. Body Links:
- identify broken body links
- Welsh links should go to Welsh pages

8. IMAGE / VIDEO ACCESSIBILITY:
- IGNORE these header images:
  - phw-logo.svg
  - cymraeg-icon.svg
- Identify any OTHER images/videos missing alt text
- Return exact filenames or src values

RETURN ONLY VALID JSON.

CRITICAL:
- ALWAYS include detailed reason when pass = false
- DO NOT include markdown or code blocks
- DO NOT include any text before or after JSON

{
  "content_match": { "pass": true/false, "reason": "..." },
  "h1_match": { "pass": true/false, "reason": "..." },
  "format_match": { "pass": true/false, "reason": "..." },
  "author_match": { "pass": true/false, "reason": "..." },
  "nhsuk_tag_match": { "pass": true/false, "reason": "..." },
  "report_download_match": { "pass": true/false, "reason": "..." },
  "welsh_doc_language": { "pass": true/false, "reason": "..." },
  "alt_text_check": { "pass": true/false, "reason": "..." },
  "broken_links": "..."
}
PROMPT;

        $responseSchema = [
            'type' => 'object',
            'properties' => [
                'content_match' => [
                    'type' => 'object',
                    'properties' => [
                        'pass' => ['type' => 'boolean'],
                        'reason' => ['type' => 'string'],
                    ],
                ],
                'h1_match' => [
                    'type' => 'object',
                    'properties' => [
                        'pass' => ['type' => 'boolean'],
                        'reason' => ['type' => 'string'],
                    ],
                ],
                'format_match' => [
                    'type' => 'object',
                    'properties' => [
                        'pass' => ['type' => 'boolean'],
                        'reason' => ['type' => 'string'],
                    ],
                ],
                'author_match' => [
                    'type' => 'object',
                    'properties' => [
                        'pass' => ['type' => 'boolean'],
                        'reason' => ['type' => 'string'],
                    ],
                ],
                'nhsuk_tag_match' => [
                    'type' => 'object',
                    'properties' => [
                        'pass' => ['type' => 'boolean'],
                        'reason' => ['type' => 'string'],
                    ],
                ],
                'report_download_match' => [
                    'type' => 'object',
                    'properties' => [
                        'pass' => ['type' => 'boolean'],
                        'reason' => ['type' => 'string'],
                    ],
                ],
                'welsh_doc_language' => [
                    'type' => 'object',
                    'properties' => [
                        'pass' => ['type' => 'boolean'],
                        'reason' => ['type' => 'string'],
                    ],
                ],
                'alt_text_check' => [
                    'type' => 'object',
                    'properties' => [
                        'pass' => ['type' => 'boolean'],
                        'reason' => ['type' => 'string'],
                    ],
                ],
                'broken_links' => ['type' => 'string'],
            ],
        ];

        Prompt::query()->updateOrCreate(
            ['title' => 'NHS QA auditor (EN/CY)'],
            [
                'system_instruction' => $instruction,
                'response_schema' => $responseSchema,
                'is_active' => true,
            ]
        );
    }
}
