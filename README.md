# wp cloudfront
`wp cloudfront` is a WP-CLI command that manage AWS CloudFront CDN distributions.

## Requires
* WP-CLI 0.23 or later
* AWS account(IAM or AWS-CLI credentials)

## Getting Started


## Subcommands

* `generate` - Generate CloudFront Distribution
* `generate_config` -  Generate CloudFront Distribution Config
* `list` - List CloudFront Distributions
* `sdk_version` - Prints current version of AWS SDK.
* `version` - Prints current version of the cli command.

## Help

```bash
NAME

  wp cloudfront

DESCRIPTION

  WP-CLI commands for the AWS CloudFront.

SYNOPSIS

  wp cloudfront <command>

SUBCOMMANDS

  generate             Generate CloudFront Distribution
  generate_config      Generate CloudFront Distribution Config
  list                 List CloudFront Distributions
  sdk_version          Prints current version of AWS SDK.
  version              Prints current version of the cli command.
```

## Installing manually

```bash
$ mkdir -p ~/.wp-cli/commands && cd -
$ git clone git@github.com:hideokamoto/wp-cli-cloudfront.git
```

Add following into your `~/.wp-cli/config.yml`.

```yaml
require:
  - commands/wp-cli-cloudfront/cli.php
```

## Upgrade

```bash
$ wp package update
```
