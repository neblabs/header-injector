# Header Injector

Dynamically inject metadata into WordPress plugin and readme headers. 

---

* **Plugin Header (`PHP`)**: Grabs the **latest tag** starting with `v` (e.g., `v1.2.0`, `v1.2.0-rc-1`, `v2.0.0-beta`), ideal for unstable/prerelease builds.
* **Readme Header (`MD/TXT`)**: Filters specifically for the **latest stable tag** matching strict SemVer (`vX.X.X`), completely ignoring unstable tags like `-rc` or `-beta`.

---

## Requirements

* PHP 8.4 or higher
* Git (must be accessible via CLI in the target system)

---

## Installation

### A - Add the package to your `composer.json`:

```bash
composer require neblabs/header-injector

```

### B - Local Installation

To download the latest compiled PHAR directly from your repository's GitHub Releases and make it executable globally on your system:

```bash
# Download the PHAR to your local bin directory
sudo curl -sSL https://github.com/neblabs/header-injector/releases/latest/download/header-injector.phar -o "$HOME"/.local/bin/header-injector

# Make it executable
sudo chmod +x "$HOME"/.local/bin/header-injector

```

### Verification

Once installed, you can invoke it from anywhere without needing PHP prefixed:

```bash
header-injector inject . ./dist 6.7 --git-source .

```


## Usage

### Command Line Interface

```bash
bin/header-injector inject <source> <target> <tested-wp-version> [--git-source GIT-SOURCE]

```

#### Arguments & Options

| Argument / Option | Required | Description |
| --- | --- | --- |
| `<source>` | **Yes** | Source directory where original files and `env.php` reside. |
| `<target>` | **Yes** | Output directory where updated files will be written. |
| `<tested-wp-version>` | **Yes** | WordPress version to inject into the `Tested up to` header. |
| `--git-source` | No | Path to the Git repository directory (defaults to current working directory `cwd`). |

If successful, writes to stdout both files in their own line.
#### Example

```bash
bin/header-injector inject ./src ./dist 6.5 --git-source=./

```

---

## Tag Resolution Rules

| Target File | Header Key | Tag Behavior | Example Output |
| --- | --- | --- | --- |
| **Plugin File** (`.php`) | `Version` | Fetches the **latest versioned tag** (including pre-releases/unstable tags). | `1.0.0-rc-1` |
| **Readme File** (`.md`/`.txt`) | `Stable tag` | Fetches the **latest stable SemVer tag** (`vX.X.X` format only). | `1.0.0` |

*Note: Leading `v` prefixes on Git tags are automatically stripped when injected.*

---

## Configuration (`env.php`)

Place an `env.php` file in your `<source>` directory to define paths and metadata defaults:

```php
<?php

return [
    'slug' => 'coupons-plus-for-woocommerce', // optional
    'files' => [
        'plugin' => [
            'in' => 'index.php',
            'out' => '((slug)).php', // Optional: defaults to 'in' if omitted
        ],
        'readme' => 'readme.md',
    ],
    'urls' => [
        'plugin' => 'https://example.com/plugins/my-plugin',
        'organization' => 'https://example.com', 
    ],
    'requires' => [
        'wp' => '6.0',
        'php' => '7.4',
    ],
];

```

---

## Continuous Integration (CI Notes)

> [!WARNING]
> If using `actions/checkout` in GitHub Actions, shallow fetching by default may only fetch the single latest commit/tag. This causes Git tag resolution to fail or report incorrect versions when working with multiple unstable tags.

### GitHub Actions Recommendation

To ensure all Git tags are present when running this tool in CI, make sure to fetch all tags before executing the command:

```yaml
# Fetch tags manually:
- name: Fetch Git Tags
  run: git fetch --tags --force

```

---

## 2. GitHub Actions Setup (CI Pipeline)

When running `header-injector` inside GitHub Actions (e.g., when generating release zips or build artifacts for a WordPress plugin), add this step to your workflow.

> [!IMPORTANT]
> You **must** set `fetch-depth: 0` on `actions/checkout` so Git downloads all tags. Without this, shallow clones will cause `git tag` to return empty results.

### Sample Workflow (`.github/workflows/build-plugin.yml`)

```yaml
name: Build Plugin Release

on:
  push:
    tags:
      - 'v*'

jobs:
  build:
    runs-on: ubuntu-latest

    steps:
      # 1. Checkout the repository with full tag history
      - name: Checkout Code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      # 2. Set up PHP environment
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4

      # 3. Download header-injector PHAR from GitHub Releases
      - name: Download Header Injector
        run: |
          curl -sSL https://github.com/neblabs/header-injector/releases/latest/download/header-injector.phar -o /usr/local/bin/header-injector
          chmod +x /usr/local/bin/header-injector

      # 4. Create target build directory and inject headers
      - name: Inject Plugin Headers
        run: |
          # inject it in the same dir
          header-injector inject . . 6.7 --git-source .

          # inject it in a different dir
          # header-injector inject . ./dist 6.7 --git-source .

```

---

### How the CI Flow Operates

1. You tag your plugin repo (e.g. `v1.2.0-rc.1` or `v1.2.0`).
2. GitHub Actions runs `actions/checkout` with full tag depth.
3. It downloads `header-injector.phar` on the fly (no need to store vendor dependencies or binaries in your plugin repo).
4. `header-injector` inspects the tags, extracts `1.2.0-rc.1` for the PHP header, `1.2.0` (or last stable) for `readme.md`, and places the injected files directly in `./dist`.

---
---

## License

MIT