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

Add the package to your `composer.json`:

```bash
composer require neblabs/header-injector

```

---

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

## License

MIT