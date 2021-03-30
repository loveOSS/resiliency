workflow "Code Quality" {
  on = "push"
  resolves = [
    "PHPStan",
    "PHP-CS-Fixer"
  ]
}

action "PHPStan" {
  uses = "docker://oskarstark/phpstan-ga:with-extensions"
  args = "analyse src --level max --configuration extension.neon"
  secrets = ["GITHUB_TOKEN"]
}

action "PHP-CS-Fixer" {
  uses = "docker://oskarstark/php-cs-fixer-ga"
  secrets = ["GITHUB_TOKEN"]
  args = "--config=.php_cs.dist --diff --dry-run"
}

action "Psalm" {
  needs="PHPStan"
  uses = "docker://mickaelandrieu/psalm-ga"
  secrets = ["GITHUB_TOKEN"]
  args = "--find-dead-code --diff"
}

action "PHPQA" {
  needs="PHP-CS-Fixer"
  uses = "docker://mickaelandrieu/phpqa-ga"
  secrets = ["GITHUB_TOKEN"]
  args = "--report --tools phpcs:0,phpmd:0,parallel-lint:0,phpmetrics,phploc,pdepend --ignoredDirs vendor"
}
