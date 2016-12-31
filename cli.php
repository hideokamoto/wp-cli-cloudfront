<?php
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}
require 'aws.phar';
use Aws\CloudFront\CloudFrontClient;


/**
 * WP-CLI commands for the AWS CloudFront.
 *
 * @subpackage commands/community
 * @maintainer Hidetaka Okamoto
 */
class WP_CLI_CloudFront extends WP_CLI_Command {
    private $version = "v0.1.0";
    private $aws_sdk_version = "v3";
    private $client;

    /**
     * Create Client
     *
     **/
    private function _create_client( string $profile , string $access_key, string $secret_key ) {
        $params = [
            'version' => 'latest',
            'region'  => 'us-east-1',
        ];
        if ( $profile ) {
            $params['profile'] = $profile;
        } elseif ( $access_key && $secret_key) {
            $params['credentials'] = [
                'key'    => $access_key,
                'secret' => $secret_key,
            ];
        } else {
            WP_CLI::error_multi_line( array(
                'Missing credentials. please set profile or access_key & secret_key.',
                '',
                'See following guides.',
                '  AWS-CLI: https://github.com/aws/aws-cli#getting-started',
                '  AWS SDK: http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html'
            ) );
            exit;
        }
        $this->client = new CloudFrontClient( $params );
    }

    /**
     * Set default Option params
     *
     **/
    private function _set_option_params( array $options ) {
        if ( file_exists( $this->getHomeDir(). '/.aws/credentials') ) {
            if ( ! isset( $options['profile'] ) ) {
                $options['profile'] = 'default';
            }
        } else {
            $options['profile'] = false;
        }
        if ( ! isset( $options['access_key'] ) ) {
            $options['access_key'] = false;
        }
        if ( ! isset( $options['secret_key'] ) ) {
            $options['secret_key'] = false;
        }
        if ( ! isset( $options['format'] ) ) {
            $options['format'] = 'json';
        }
        return $options;
    }

    /**
     * List CloudFront Distributions
     *
     * ##OPTIONS
     * [--profile=<profile>]
     * : AWS-CLI's profile name
     *
     * [--access_key=<access_key>]
     * : IAM Access Key
     *
     * [--secret_key=<secret_key>]
     * : IAM Secret Key
     *
     * [--format=<format>]
     * : Render results in a specific format (Now only JSON support)
     * @when before_wp_load
     */
    function list( $args, $assoc_args ) {
        $options = $this->_set_option_params( $assoc_args );
        $this->_create_client( $options['profile'], $options['access_key'], $options['secret_key'] );
        $result = $this->client->listDistributions();
        //@TODO Support another format like YAML / table / csv and more
        //echo json_encode($result->get('DistributionList'));
    }

    /**
     * Get user's home Directory
     * @return null|string
     * via:https://github.com/aws/aws-sdk-php/blob/f4cf827159170119b9df502a5e91fd42b6c2cf45/src/Credentials/CredentialProvider.php#L320-L337
     **/
    private function getHomeDir()
    {
        // On Linux/Unix-like systems, use the HOME environment variable
        if ($homeDir = getenv('HOME')) {
            return $homeDir;
        }
        // Get the HOMEDRIVE and HOMEPATH values for Windows hosts
        $homeDrive = getenv('HOMEDRIVE');
        $homePath = getenv('HOMEPATH');
        return ($homeDrive && $homePath) ? $homeDrive . $homePath : null;
    }

    /**
     *
     *
     **/
    private function generate_distribution_config( $args, $assoc_args ) {
        if ( isset( $assoc_args['domain'] ) ) {
            $domain = $assoc_args['domain'];
        } else {
            $domain = trim( cli\prompt(
                'Your website Domain',
                $default = false,
                $marker = ': ',
                $hide = false
            ) );
        }
        $domain = esc_attr( $domain );

        if ( isset( $assoc_args['origin'] ) ) {
            $origin = $assoc_args['origin'];
        } else {
            $origin = trim( cli\prompt(
                'Your origin webserver address',
                $default = false,
                $marker = ': ',
                $hide = false
            ) );
        }
        $origin = esc_attr( $origin );
        $result = wp_remote_get('https://raw.githubusercontent.com/amimoto-ami/create-cf-dist-settings/master/cloudfront-input.json');
        if ( is_wp_error( $result ) ) {
            WP_CLI::error( $result->get_error_message() );
        }
        $config = $result['body'];
        $config = preg_replace( '/example.com/', $domain, $config );
        $config = preg_replace( '/%origin-id%/', $domain, $config );
        $config = preg_replace( '/origin.example.com/', $origin, $config );
        $config = preg_replace( '/%caller-reference%/', time(), $config );
        return $config;
    }

    /**
     * Generate CloudFront Distribution
     *
     * ##OPTIONS
     * [--domain=<domain>]
     * : Your website domain
     *
     * [--origin=<origin>]
     * : Your origin webserver address
     *
     * [--profile=<profile>]
     * : AWS-CLI's profile name
     *
     * [--acccess_key=<access_key>]
     * : IAM Access Key
     *
     * [--secret_key=<secret_key>]
     * : IAM Secret Key
     * @when after_wp_load
     */
    function generate( $args, $assoc_args ) {
        $config = $this->generate_distribution_config( $args, $assoc_args );
        echo $config;
    }
    /**
     * Generate CloudFront Distribution Config
     *
     * ##OPTIONS
     * [--domain=<domain>]
     * : Your website domain
     *
     * [--origin=<origin>]
     * : Your origin webserver address
     *
     * @when after_wp_load
     */
    function generate_config( $args, $assoc_args ) {
        $config = $this->generate_distribution_config( $args, $assoc_args );
        echo $config;
    }

    /**
     * Prints current version of the cli command.
     *
     * @when before_wp_load
     */
    public function version(){
        WP_CLI::line( $this->version );
    }

    /**
     * Prints current version of AWS SDK.
     *
     * @when before_wp_load
     */
    public function sdk_version(){
        WP_CLI::line( $this->aws_sdk_version );
    }
}
WP_CLI::add_command( 'cloudfront', 'WP_CLI_CloudFront' );
