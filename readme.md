# Header Injector

Dynamically inject metadata into WordPress plugin and readme headers.

## Why?

Because nobody wants to update these values manually. It is a pain to update the version manually on every release. By automating this you ensure your plugin and readme files are always in sync and your versions are always updated accordingly. Add this to your CI/CD pipeline and forget about updating these header values ever again. Good riddance to that! Also this package was 100% handwritten :).

---

* **Plugin Header (`PHP`)**: Grabs the **latest git tag** starting with `v` (e.g., `v1.2.0`, `v1.2.0-rc-1`, `v2.0.0-beta`)
* **Readme Header (`MD/TXT`)**: Uses the **latest stable tag** matching SemVer (`vX.X.X`), completely ignoring unstable tags like `-rc` or `-beta`.

---

# Example

Source: 
```php
<?php
/*
* Plugin Name:       Coupons+
* Plugin URI:        unknown
* Description:       Next-generation coupon offers engine for WooCommerce. Create advanced deals, smart BOGO offers, and more!
* Version:           dev
* Author:            neblabs
* Requires at least: unknown
* Requires PHP:      unknown
* License: GPLv3
  */
```

Becomes

```php
<?php
/*
* Plugin Name:       Coupons+
* Plugin URI:        example.com
* Description:       Next-generation coupon offers engine for WooCommerce. Create advanced deals, smart BOGO offers, and more!
* Version:           3.3.1-beta-1
* Author:            neblabs
* Author URI:        neblabs.com
* Requires at least: 5.8
* Requires PHP:      7.4
* License: GPLv3
  */
```
...And

```md
=== Coupons+ ===
Contributors: neblabs
Tags: woocommerce, coupons, bogo, discounts, pricing
Requires at least: unknown
Tested up to: unknown
Stable tag: unknown
Requires PHP: unknown
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
```

Becomes...

```md
=== Coupons+ ===
Contributors: neblabs
Tags: woocommerce, coupons, bogo, discounts, pricing
Requires at least: 5.8
Tested up to: 7.0.1
Stable tag: 3.3.0
Requires PHP: 7.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
```

All injected and synced automatically!


## Requirements

* PHP 8.4 or higher
* Git (must be accessible via CLI in the target system). Your repository must be using tags in the format vX.X.X.

---

## Installation

### Local Installation

Run the installer
```bash
curl -L https://raw.githubusercontent.com/neblabs/header-injector/main/bin/install.sh | sh
```

Or download the latest compiled PHAR directly and make it executable globally:

```bash
sudo curl -sSL https://github.com/neblabs/header-injector/releases/latest/download/header-injector.phar -o "$HOME"/.local/bin/header-injector

sudo chmod +x "$HOME"/.local/bin/header-injector

```

### Verification

Once installed, you can invoke it from anywhere:

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

## Headers map & data sources
### 1. Plugin File Headers (`.php`)

| Header Key | Data Source / Resolution Logic                                                                                   | Example Source |
| --- |------------------------------------------------------------------------------------------------------------------| --- |
| **Plugin URI** | `urls.plugin` (env.php)                                                                                          | `'[https://example.com/plugin](https://example.com/plugin)'` |
| **Version** | Latest Git tag starting with `v[0-9]` *(includes unstable tags like `rc`, `beta`, `alpha`)*. Strips leading `v`. | Git tag `v1.2.0-rc-1` |
| **Author URI** | `urls.organization` (env.php)                                                                                    | `'[https://example.com](https://example.com)'` |
| **Requires at least** | `requires.wp` (env.php)                                                                                          | `'6.0'` |
| **Requires PHP** | `requires.php` (env.php)                                                                                                  | `'7.4'` |

---

### 2. Readme File Headers (`.md` / `readme.txt`)

| Header Key | Data Source / Resolution Logic                                                                                       | Example Source |
| --- |----------------------------------------------------------------------------------------------------------------------| --- |
| **Requires at least** | `requires.wp` (env.php)                                                                                              | `'6.0'` |
| **Tested up to** | CLI Argument `<tested-wp-version>`                                                                                   | CLI input `7.0.6` |
| **Stable tag** | Latest **stable** SemVer Git tag matching `^v[0-9]+\.[0-9]+\.[0-9]+$` *(ignores unstable tags)*. Strips leading `v`. | Git tag `v1.1.0` *(skips `v1.2.0-beta`)* |
| **Requires PHP** | `requires.php` (env.php)                                                                                                      | `'7.4'` |

> **Note:** Any header value resolved as `null`, `false`, or `'unknown'` is automatically filtered out and omitted from the target file during injection.

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
> You **must** fetch all the tags or set `fetch-depth: 0` on `actions/checkout` so Git downloads all tags. Without this, shallow clones will cause `git tag` to return empty results.

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
      # Fetch tags manually:
      - name: Fetch Git Tags
        run: git fetch --tags --force

      # 2. Set up PHP environment
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4

      # 3. Download header-injector PHAR from GitHub Releases
      - name: Download Header Injector
        run: |
          curl -L https://raw.githubusercontent.com/neblabs/header-injector/main/bin/install.sh | sh

      # 4. Create target build directory and inject headers
      - name: Inject Plugin Headers
        run: |
          # inject it in the same dir
          "$HOME"/.local/bin/header-injector inject . . "$testedWPVersion" --git-source .

          # inject it in a different dir
          # "$HOME"/.local/bin/header-injector inject . ./dist "$testedWPVersion" --git-source .
          
          # "$testedWPVersion" in practice should come from the latest version installed in the env, its all automated! Literally set and forget :)

```

---

## License

MIT