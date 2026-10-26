<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sends baseline security headers for every web request.
 * HSTS is only sent when FORCE_HTTPS=true in .env (enable after HTTPS is confirmed).
 */
class Security_headers
{
	public function send()
	{
		if (is_cli() OR headers_sent())
		{
			return;
		}

		if (env('FORCE_HTTPS', FALSE) && ! is_https())
		{
			header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], TRUE, 301);
			exit;
		}

		// In production, send www/non-www or IP visits to the APP_URL host so session cookies and canonical URLs match.
		$app_host = parse_url((string) env('APP_URL', ''), PHP_URL_HOST);
		$req_host = isset($_SERVER['HTTP_HOST']) ? strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'])) : '';
		if (env('APP_ENV', 'production') === 'production' && $app_host && $req_host && $req_host !== strtolower($app_host))
		{
			header('Location: '.rtrim((string) env('APP_URL'), '/').$_SERVER['REQUEST_URI'], TRUE, 301);
			exit;
		}

		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: SAMEORIGIN');
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
		header('Cross-Origin-Opener-Policy: same-origin');
		header_remove('X-Powered-By');

		// CSP: self-hosted assets; Google analytics/tag manager/maps/reCAPTCHA/YouTube allowed
		// because they are configurable integrations. 'unsafe-inline' is needed for admin-managed
		// tracking snippets; tighten to nonces if no inline scripts are used.
		$csp = array(
			"default-src 'self'",
			"script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://www.google-analytics.com https://www.google.com https://www.gstatic.com",
			"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
			"font-src 'self' data: https://fonts.gstatic.com",
			"img-src 'self' data: https:",
			"connect-src 'self' https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com",
			"frame-src https://www.google.com https://maps.google.com https://www.youtube-nocookie.com https://www.youtube.com https://player.vimeo.com",
			"object-src 'none'",
			"base-uri 'self'",
			"form-action 'self'",
			"frame-ancestors 'self'",
		);
		header('Content-Security-Policy: '.implode('; ', $csp));

		if (env('FORCE_HTTPS', FALSE))
		{
			header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	}
}
