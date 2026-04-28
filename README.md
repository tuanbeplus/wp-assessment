# WP Assessment (Saturn Project)

Custom WordPress plugin for the **Australian Disability Network (AND) Project Saturn**. This plugin handles complex organizational assessments, multi-tier scoring, ranking systems, and automated PDF report generation.

**Version:** 3.0.4

---

## Plugin Ecosystem

The plugin manages two primary types of assessments, each with its own submission and reporting workflow:

### 1. Index Submissions (`submissions`)

- **Purpose:** Comprehensive multi-year accessibility index.
- **Scoring Formulas:** Supports dual scoring:
  - **Index 2024 (Raw):** Sum of raw points (0-4). Stores in `sum` and `percent`.
  - **Index 2023 (Weighted):** Sum of (point \* weighting). Stores in `sum_with_weighting` and `percent_with_weighting`.
- **Ranking:** Advanced cross-organization benchmarking and industry ranking.
- **Meta Keys:** `total_submission_score`, `total_and_score`, `total_agreed_score`.

### 2. DCR Submissions (`dcr_submissions`)

- **Purpose:** Digital Content Review.
- **Version Control:** Tracks multiple versions of a single review (Latest vs. Historical).
- **Workflows:** Focuses on preliminary draft reports and detailed feedback per content item.

---

## Data Architecture

### Custom Database Tables

To handle granular answer data beyond standard meta-data, the plugin initializes two custom tables:

- `{wp_prefix}_user_quiz_submissions`: Stores every answer, attachment link, and evaluator feedback for Index assessments.
- `{wp_prefix}_dcr_quiz_submissions`: Stores granular data for Digital Content Review assessments.

### Key Shared Post Types

- `assessments`: The builder for all questionnaires.
- `reports` / `dcr_reports`: Generated PDF metadata and dashboard stats.
- `ranking`: Aggregated standings (Industry, Total Score, Framework).

---

## Integrations

### Salesforce

- Synchronizes user and organizational data via `Salesforce ID`.
- Handles `Saturn Invites` to trigger assessment workflows.
- Webhooks update local submission status based on Salesforce events.

### Azure Storage

- Offloads assessment attachments to Azure Cloud for better scalability and security.

### mPDF Engine

- Uses `vendor/autoload.php` (mPDF) to generate high-fidelity, highly formatted PDF reports including dynamic Charts (Chart.js) captured as images.

---

## Developer & AI Agent Guide

### Critical Maintenance Files

- `includes/custom-fields.php`: Primary save logic. Look for `on_save_submission_custom_fields` (Index) and `on_save_dcr_submission` (DCR).
- `includes/helper.php`: Scoring engines and max score calculations.
- `includes/function.php`: Core class `WP_Assessment`, table initialization, and global filtering logic.
- `includes/ranking-functions.php`: The "brain" of the ranking system.

### Admin View Locations

- `views/admin/submissions/`: Check `submission-scoring.php` (Index) and `submission-dcr-view.php` (DCR).
- `views/admin/assessments/`: Questionnaire builder and formula options.

### Important Note for Updates

When updating scoring logic:

1.  **Submission Refresh:** Admins MUST click **Update** on existing Submissions. The code handles `max_input_vars` limitations by falling back to existing database meta if fields are missing.
2.  **Ranking Refresh:** Ranking posts must be re-saved to update the leaderboard.

---

_Copyright (c) 2026 YSN! All Rights Reserved_
