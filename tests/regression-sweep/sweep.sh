#!/usr/bin/env bash
#
# Regression sweep: cycles through theme/plugin combos on the eeb-dev DDEV site
# and asserts that the rendered HTML matches expected patterns.
#
# Usage: ./tests/regression-sweep/sweep.sh
#
# Exit code: 0 if all checks pass, 1 otherwise.

set -uo pipefail

readonly DDEV_DIR="$HOME/Projects/eeb-dev"
readonly BASE_URL="https://eeb-dev.ddev.site"
readonly DIVI_ZIPS_DIR="${EEB_DIVI_ZIPS_DIR:-$HOME/Projects/eeb-test-assets/divi}"
readonly EEB_OPTION="WP_Email_Encoder_Bundle_options"

# Themes to cycle through. Add more by appending to this array.
# Special entries (force-installed from a local zip before activation):
#   divi-4 — installs $DIVI_ZIPS_DIR/divi-builder-4.zip then activates "Divi"
#   divi-5 — installs $DIVI_ZIPS_DIR/divi-builder-5.zip then activates "Divi"
THEMES=(twentytwentyfive twentytwentyone astra graphene hello-elementor divi-4 divi-5)

# Plugin combos. Each entry is a comma-separated list of plugins to activate
# (in addition to email-encoder-bundle, which stays active throughout).
# Use "none" to test with only EEB active.
#
# Premium add-ons (drop zips next to the Divi ones to enable):
#   - bricks  — needs Bricks license
#   - Avada   — needs ThemeForest license
PLUGIN_COMBOS=(none wpforms-lite contact-form-7 elementor beaver-builder-lite-version)

# Check definitions. Each check is: URL|description|must_not_contain_regex|must_contain_regex|applies_to_modes
# applies_to_modes is a comma-separated list of protection modes the check applies to.
# Use "*" to apply in all modes. Use empty string for any pattern field that does not apply.
CHECKS=(
  # Firefox <select><option> fix — only relevant in modes that emit HTML wrappers
  "/firefox-select-repro/|Option contents have no <script> wrapper|<option[^>]*>[^<]*<script||with_javascript"
  "/firefox-select-repro/|Option contents have no <span> wrapper|<option[^>]*>[^<]*<span||with_javascript,without_javascript"
  "/firefox-select-repro/|<select> renders on page||<select|*"

  # Mailto links: encoded properly in all modes
  "/regression-check/|Mailto: no literal mailto:address remains|<a[^>]*href=\"mailto:[^\"]*@example\\.com[^\"]*\"||with_javascript,without_javascript"
  "/regression-check/|Mailto: data-enc-email present||data-enc-email|with_javascript"

  # Textarea content shouldn't have HTML wrappers
  "/regression-check/|Textarea contents have no <script> wrapper|<textarea[^>]*>[^<]*<script|<textarea|with_javascript"
  "/regression-check/|Textarea contents have no <span> wrapper|<textarea[^>]*>[^<]*<span||with_javascript,without_javascript"

  # Plain emails: no literal *@example.com text content (matches all our test emails)
  "/regression-check/|Plain emails are encoded (no literal *@example.com text)|>[^<]*[a-z]+@example\\.com[^<]*<||*"

  # Image encoding mode — emails should render as <img src="...?eeb_mail=...">
  "/regression-check/|Image mode: emails rendered as <img> tags||<img[^>]+eeb_mail=|image_encoding"
  "/regression-check/|Image mode: image URL contains hash signature||eeb_hash=|image_encoding"
  "/firefox-select-repro/|Image mode: option contents have no <img> wrapper|<option[^>]*>[^<]*<img||image_encoding"
)

# Protection modes to cycle through. Each one exercises a different code path
# in the plugin (different encoding strategy / output structure).
#
# "image_encoding" is a virtual mode that flips both protect_using=with_javascript
# AND convert_plain_to_image=1. The plugin replaces plain emails with <img> tags
# pointing at a PHP-generated PNG. All other modes run with image conversion off.
PROTECTION_MODES=(with_javascript without_javascript strong_method char_encode image_encoding)

# State tracking
PASS_COUNT=0
FAIL_COUNT=0
FAIL_DETAILS=()
SKIP_COUNT=0
ORIGINAL_THEME=$(cd "$DDEV_DIR" && ddev exec "wp theme list --status=active --field=name" 2>/dev/null | tr -d '\r\n')
ORIGINAL_OPTIONS=$(cd "$DDEV_DIR" && ddev exec "wp option get $EEB_OPTION --format=json" 2>/dev/null | tr -d '\r\n')

color_ok()   { printf "\033[32m%s\033[0m" "$1"; }
color_bad()  { printf "\033[31m%s\033[0m" "$1"; }
color_dim()  { printf "\033[90m%s\033[0m" "$1"; }

run_ddev() {
  (cd "$DDEV_DIR" && ddev exec "$@" >/dev/null 2>&1)
}

activate_theme() {
  case "$1" in
    divi-4|divi-5)
      local version="${1#divi-}"
      local zip="$DIVI_ZIPS_DIR/divi-builder-${version}.zip"
      if [[ ! -f "$zip" ]]; then
        echo "  $(color_bad "✗") Skipping $1 — zip not found at $zip"
        return 1
      fi
      # Both versions unpack to the same theme slug "Divi"; force-install swaps them.
      cp "$zip" "$DDEV_DIR/.divi-temp.zip"
      run_ddev "wp theme install --force .divi-temp.zip"
      rm -f "$DDEV_DIR/.divi-temp.zip"
      run_ddev "wp theme activate Divi"
      ;;
    *)
      run_ddev "wp theme activate $1"
      ;;
  esac
}

deactivate_all_test_plugins() {
  for combo in "${PLUGIN_COMBOS[@]}"; do
    [[ "$combo" == "none" ]] && continue
    IFS=',' read -ra plugins <<< "$combo"
    for p in "${plugins[@]}"; do
      run_ddev "wp plugin deactivate $p"
    done
  done
}

activate_plugin_combo() {
  deactivate_all_test_plugins
  [[ "$1" == "none" ]] && return
  IFS=',' read -ra plugins <<< "$1"
  for p in "${plugins[@]}"; do
    run_ddev "wp plugin activate $p"
  done
}

set_protection_mode() {
  case "$1" in
    image_encoding)
      run_ddev "wp option patch update $EEB_OPTION protect_using with_javascript"
      run_ddev "wp option patch update $EEB_OPTION convert_plain_to_image 1"
      ;;
    *)
      run_ddev "wp option patch update $EEB_OPTION protect_using $1"
      run_ddev "wp option patch update $EEB_OPTION convert_plain_to_image 0"
      ;;
  esac
}

check_applies_to_mode() {
  local applies="$1" mode="$2"
  [[ "$applies" == "*" ]] && return 0
  IFS=',' read -ra modes <<< "$applies"
  for m in "${modes[@]}"; do
    [[ "$m" == "$mode" ]] && return 0
  done
  return 1
}

run_check() {
  local theme="$1" combo="$2" mode="$3" url="$4" desc="$5" must_not="$6" must="$7"
  local body_file
  body_file=$(mktemp)
  # Strip JSON-LD blocks before grepping — the plugin intentionally leaves
  # <script> content alone, so themes that emit ld+json with the page
  # description will leak emails there. Tracked as a separate known gap.
  curl -sk "$BASE_URL$url" 2>/dev/null \
    | perl -0777 -pe 's|<script[^>]*type="application/ld\+json"[^>]*>.*?</script>||gs' \
    > "$body_file"

  local result="PASS"
  local reason=""

  if [[ -n "$must_not" ]] && grep -qE "$must_not" "$body_file"; then
    result="FAIL"
    reason="found forbidden pattern: $must_not"
  fi

  if [[ "$result" == "PASS" && -n "$must" ]] && ! grep -qE "$must" "$body_file"; then
    result="FAIL"
    reason="missing required pattern: $must"
  fi

  rm -f "$body_file"

  if [[ "$result" == "PASS" ]]; then
    PASS_COUNT=$((PASS_COUNT + 1))
    printf "    %s %s\n" "$(color_ok "✓")" "$(color_dim "$desc")"
  else
    FAIL_COUNT=$((FAIL_COUNT + 1))
    FAIL_DETAILS+=("[$theme / $combo / $mode] $desc — $reason")
    printf "    %s %s\n" "$(color_bad "✗")" "$desc"
    printf "      %s\n" "$(color_bad "$reason")"
  fi
}

cleanup() {
  echo
  echo "Restoring original state..."
  deactivate_all_test_plugins
  [[ -n "$ORIGINAL_THEME" ]] && activate_theme "$ORIGINAL_THEME"
  if [[ -n "$ORIGINAL_OPTIONS" ]]; then
    # Restore EEB settings (most importantly protect_using) by writing the original JSON back.
    local tmp
    tmp=$(mktemp)
    echo "$ORIGINAL_OPTIONS" > "$tmp"
    (cd "$DDEV_DIR" && ddev exec "wp option update $EEB_OPTION --format=json < $(basename "$tmp")" >/dev/null 2>&1) || true
    # Fallback: just restore the protect_using key which is the one we mutated.
    local original_mode
    original_mode=$(echo "$ORIGINAL_OPTIONS" | grep -oE '"protect_using":"[^"]+"' | cut -d'"' -f4)
    [[ -n "$original_mode" ]] && set_protection_mode "$original_mode"
    rm -f "$tmp"
  fi
}
trap cleanup EXIT

echo "=== Email Encoder regression sweep ==="
echo "Original theme: $ORIGINAL_THEME"
echo "Themes: ${#THEMES[@]} | Plugin combos: ${#PLUGIN_COMBOS[@]} | Modes: ${#PROTECTION_MODES[@]}"
echo

for theme in "${THEMES[@]}"; do
  for combo in "${PLUGIN_COMBOS[@]}"; do
    activate_theme "$theme"
    activate_plugin_combo "$combo"

    for mode in "${PROTECTION_MODES[@]}"; do
      echo "── theme=$theme  plugins=$combo  mode=$mode ──"
      set_protection_mode "$mode"

      # Warmup curl + brief pause so first-load redirects, cache primes, and
      # plugin/theme one-time setup finish before we assert on output.
      curl -sk "$BASE_URL/" >/dev/null 2>&1
      sleep 1

      for check in "${CHECKS[@]}"; do
        IFS='|' read -r url desc must_not must applies <<< "$check"
        if ! check_applies_to_mode "$applies" "$mode"; then
          SKIP_COUNT=$((SKIP_COUNT + 1))
          continue
        fi
        run_check "$theme" "$combo" "$mode" "$url" "$desc" "$must_not" "$must"
      done
    done
    echo
  done
done

echo "=== Summary ==="
echo "$(color_ok "Passed: $PASS_COUNT")"
echo "$(color_bad "Failed: $FAIL_COUNT")"
echo "$(color_dim "Skipped (mode-irrelevant): $SKIP_COUNT")"

if (( FAIL_COUNT > 0 )); then
  echo
  echo "Failures:"
  for f in "${FAIL_DETAILS[@]}"; do
    echo "  - $f"
  done
  exit 1
fi
