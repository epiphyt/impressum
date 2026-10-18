<?php

declare(strict_types=1);

/**
 * Minimal stand-ins for the REST classes of WordPress.
 *
 * Only what the settings controller touches is implemented. The signatures of
 * WP_REST_Controller are reproduced faithfully – its parameters carry no type
 * hint, and a subclass adding one would be a fatal error.
 */

if (!\class_exists('WP_REST_Server')) {
    class WP_REST_Server
    {
        public const READABLE = 'GET';

        public const CREATABLE = 'POST';

        public const EDITABLE = 'POST, PUT, PATCH';

        public const DELETABLE = 'DELETE';
    }
}

if (!\class_exists('WP_REST_Request')) {
    class WP_REST_Request
    {
        /**
         * @var array<string, mixed>
         */
        private array $params;

        /**
         * @param array<string, mixed> $params Request parameters
         */
        public function __construct(array $params = [])
        {
            $this->params = $params;
        }

        public function get_param(string $key): mixed
        {
            return $this->params[$key] ?? null;
        }

        public function set_param(string $key, mixed $value): void
        {
            $this->params[$key] = $value;
        }
    }
}

if (!\class_exists('WP_REST_Response')) {
    class WP_REST_Response
    {
        public mixed $data;

        public function __construct(mixed $data = null)
        {
            $this->data = $data;
        }

        public function get_data(): mixed
        {
            return $this->data;
        }
    }
}

if (!\class_exists('WP_REST_Controller')) {
    abstract class WP_REST_Controller
    {
        protected string $namespace = '';

        protected string $rest_base = '';

        public function register_routes()
        {
            return null;
        }

        public function get_item($request)
        {
            return null;
        }

        public function get_item_permissions_check($request)
        {
            return true;
        }

        public function update_item($request)
        {
            return null;
        }

        public function update_item_permissions_check($request)
        {
            return true;
        }

        public function get_public_item_schema()
        {
            return [];
        }
    }
}
