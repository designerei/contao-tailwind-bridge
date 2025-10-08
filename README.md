# Contao Tailwind Bridge

A structured bridge between **Contao CMS** and **Tailwind CSS**.  
This bundle provides a consistent way to define, resolve, and debug Tailwind configuration, utilities, and field mappings directly from YAML files.

## 📁 Configuration Structure

All configuration files are located under:

```
config/tailwind_bridge/
```

The directory must contain the following files:

```
theme.yaml
utilities.yaml
fields.yaml
```

## 🎨 theme.yaml
Defines global theme tokens and Tailwind-related configuration.

```yaml
theme:
  # Optional prefix for all generated classes.
  # Example: "tw" results in "tw-m-1" or "md:tw-mt-2"
  prefix: '<prefix>'

  # Responsive breakpoints used for utility generation.
  # Each will prepend a responsive variant (e.g. sm:, md:, lg:).
  breakpoints: ['<breakpoint-1>', '<breakpoint-2>', '<breakpoint-3>']

  # Spacing scale used by utilities like margin and padding.
  # Referenced via "theme.spacing" inside utilities.yaml.
  spacing: ['<value-1>', '<value-2>', '<value-3>', ...]

  # Safelist configuration for Tailwind CSS.
  safelist:
    # Directory where the generated safelist file will be stored.
    dir: '<output-directory>' # e.g. var/tailwind

    # Name of the generated file (without extension).
    filename: '<filename>'

    # Whether the safelist file should be minified (single-line output).
    minified: true | false
```

## 🧩 utilities.yaml
Defines reusable Tailwind-style utility patterns. Each utility describes how class names are generated.

```yaml
utilities:
  <utility_name>:
    # One or more class name prefixes.
    # Example: 'm' generates `m-0`, `m-1`, etc.
    # Multiple prefixes (like 'mx', 'my', 'mt', etc.) are allowed.
    names: ['<prefix>', '<another-prefix>']

    # Defines which values to append to the names.
    # Can reference a theme variable (e.g. theme.spacing)
    # or be an explicit list of values.
    values: 'theme.<token>' | ['<value-1>', '<value-2>', ...]

    # Whether to generate responsive variants for breakpoints
    # defined in theme.yaml (e.g. sm:, md:, lg:).
    responsive: true | false
```

## 🧠 fields.yaml
Defines which backend fields (e.g. Contao DCA fields) are populated or extended by Tailwind utilities or custom CSS classes.

```yaml
fields:
  <field_name>:
    # List of sources for generating CSS class options:
    # - utilities.<key> → references a utility name from utilities.yaml
    # - <string> → manually defined custom class name
    options:
      - 'utilities.<utility_name>'
      - '<custom-class>'

    # Optional: Default value for this field
    # Will automatically include the prefix if defined in the theme.
    default: '<default-class>'

    # Optional: Key-value pairs for backend label overrides.
    # Keys are CSS class names (with prefix if applicable)
    # Values are the human-readable labels displayed in Contao.
    reference:
      '<class-name>': '<Label>'
      '<another-class>': '<Label>'
```

## Commands

### Debug

```
bin/console tailwind:debug:theme
bin/console tailwind:debug:utilities
bin/console tailwind:debug:fields
```

### Generate Safelist

```
bin/console tailwind:generate:safelist
```
