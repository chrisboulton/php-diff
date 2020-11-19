# changelog

## 2.3.0 (2020-11-19)

- Add: Change log.
- Fix: Html SideBySide renders equal lines of version 1 at both sides (Option ignoreCase).
- Fix: Second parameter of string repeat function minimizes to 0.
- Fix: #60 - Unified Cli renderer options incompatible with Main renderer options
- Fix: #64 - Calculation of maxLineMarkerWidth independent of input format.
- Add: Similarity calculation
- Add: New marking levels for inline differences
- Add: Html merged renderer

## 2.2.1 (2020-08-06)

- Fix: #58 - Side by side diff shows empty diff

## 2.2.0 (2020-07-23)

- Add: Option for a custom override renderer. #53
- Add: No output when there are no differences between the compared strings / files. #52 #54

## 2.1.1 (2020-07-17)

- Fix: #50 - Renderers produce output with equal texts, while they shouldn't.

## 2.1.0 (2020-07-13)

- Add: Cli uncolored output. This allows it to be piped.

## 2.0.0 (2020-07-09)

- Add: Unified Commandline colored output.
- Change: switch to semantic versioning.

## 1.18 (2020-07-01)

- Add: A dark theme to the example.
- Fix: Avoid variables with short names (some).

## 1.17 (2020-06-08)

- Fix #32 - Side by side diff shows only partially all deleted lines.

## 1.16 (2020-03-02)

- Features
    - Add: option trimEqual.

- Fixes
    - Fix PHPMD Violation.
    - Code Optimization, cleanup, refactoring and commenting.

## 1.15 (2020-01-24)

- Add: New Unified HTML.
- Fix: Code clean up.

## 1.14 (2019-12-03)

- Fix: Remove some old dead code.

## 1.13 (2019-10-08)

- Change: Switch to PSR12.

## 1.12 (2019-03-18)

- Change: Update Composer Configuration.
- Fix: PSR-2 conventions.

## 1.11 (2019-02-22)

- Fix: Code clean up.
- Fix: Composer autoloader for unit tests.

## 1.10 (2019-02-20)

- Fix: Code clean up.

## 1.9 (2019-02-19)

- Fix: Code clean up.

## 1.8

- Change: Update Readme and bumping versions.
- Fix: Moved include of Autoloader from the constructor to global space for HtmlArray unit test.

## 1.7

- Fix: PSR-2 code alignment.

## 1.6

- Change: Bump required version of PHP to v7.1.
- Add: Return type hinting.

## 1.5 (2019-01-15)

- Fix: Autoloader naming issues.

## 1.4 (2019-01-14)

- Add: PSR-4 namespace support.

## 1.3 (2019-01-11)

- Fix: PHP methods contained too much logic. That has been simplified.

## 1.2 (2018-01-23)

- Add: Support for custom titles.

## 1.1 (2017-05-06)

- Fix: Wrong highlight area for chinese characters.

## 1.0 

- Initial version.
