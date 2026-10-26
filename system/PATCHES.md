# Local patches to CodeIgniter 3.1.13

`#[\AllowDynamicProperties]` was added above every `class CI_*` declaration so the
framework runs on PHP 8.2+ without deprecation notices (CI 3.1.13 creates dynamic
properties). The attribute is ignored by PHP < 8.0. Re-apply after upgrading CI:

    grep -rlE "^(abstract )?class CI_" --include=*.php system | xargs sed -i -E 's/^((abstract )?class CI_)/#[\\AllowDynamicProperties]\n\1/'
