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

Compare these pages (cleaned HTML structure provided, not plain text):

English: ${english}
Welsh: ${welsh}

IMPORTANT RULES:
- DO NOT compare file sizes
- Ignore schema metadata creators
- Ignore URLs containing https://beta-icc.gig.cymru/news/
- Authors block:
  - OK if missing on BOTH pages
  - FAIL only if present AND different
- The content provided is CLEANED HTML - examine the HTML tags and structure
- If a line starts with "[English page unavailable for QA" or "[Welsh page unavailable for QA", that side failed automated fetch — FAIL content_match and other structural checks with a clear reason; do not treat the bracket text as real page content.
- If the message includes MACHINE_VERIFIED (unauthenticated HEAD/GET from QA worker), trust those lines for whether each listed download URL was reachable without cookies. You cannot open binary files yourself; do not contradict MACHINE_VERIFIED outcomes for download reachability.

CHECK:

1. Content match (same meaning)
- the same content is on both pages but of different language
- identify any duplications, inconsistencies or potential issues
- compare the semantic structure (headings, paragraphs, lists, etc.)

2. H1 matches URL slug
- Examine the <h1> tag content
- Check if it corresponds to the URL slug structure
- Example: URL "additional-ahp-investment-mid-year-report" should have H1 "Additional AHP investment mid-year report"

3. Formatting matches - e.g text, tables, styling etc.
- Compare HTML structure between EN and CY pages
- Check for consistent use of <p>, <ul>/<ol>, <table>, etc.
- Verify similar heading hierarchy (h1, h2, h3)

4. Authors match (only if both present)

5. nhsuk-tag content matches
- Look for elements with class="nhsuk-tag" or class="nhsuk-tag--white"
- Compare the text content of these tags between EN and CY

6. Report downloads:
- ONLY check if section exists
- If present:
  - filename must match H1
  - use MACHINE_VERIFIED results when provided for whether the download URL responded OK without authentication; otherwise infer only from HTML (weaker)
  - Welsh file must contain Welsh language
- If not present → PASS

7. Body Links:
- identify broken body links (check href attributes in <a> tags)
- Welsh links should go to Welsh pages (beta-icc.gig.cymru domain)
- English links should go to English pages (beta-phw.nhs.wales domain)

8. IMAGE / VIDEO ACCESSIBILITY:
- IGNORE these header images:
  - phw-logo.svg
  - cymraeg-icon.svg
- Identify any OTHER <img> tags missing alt="..." attribute
- Return exact src values for images with missing alt text

RETURN ONLY VALID JSON.

CRITICAL:
- You MUST return every JSON key listed below on every response. Never omit a key.
- For each object check: always include both "pass" (boolean) and "reason" (non-empty string). When there is no issue, say so clearly (e.g. "No problems found for this check.").
- For "broken_links": always output one non-empty sentence (e.g. "No broken links found." or describe any problems). Never leave it blank.
- ALWAYS include detailed reason when pass = false
- DO NOT include markdown or code blocks
- DO NOT include any text before or after JSON
- Use the HTML tags to understand the structure (e.g., <h1>, <p>, <ul>, <a href="...">, <img src="..." alt="...">)

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

        $objectCheck = static fn (): array => [
            'type' => 'object',
            'properties' => [
                'pass' => [
                    'type' => 'boolean',
                ],
                'reason' => [
                    'type' => 'string',
                ],
            ],
        ];

        $responseSchema = [
            'type' => 'object',
            'properties' => [
                'content_match' => $objectCheck(),
                'h1_match' => $objectCheck(),
                'format_match' => $objectCheck(),
                'author_match' => $objectCheck(),
                'nhsuk_tag_match' => $objectCheck(),
                'report_download_match' => $objectCheck(),
                'welsh_doc_language' => $objectCheck(),
                'alt_text_check' => $objectCheck(),
                'broken_links' => [
                    'type' => 'string',
                ],
            ],
            'required' => [
                'content_match',
                'h1_match',
                'format_match',
                'author_match',
                'nhsuk_tag_match',
                'report_download_match',
                'welsh_doc_language',
                'alt_text_check',
                'broken_links',
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
