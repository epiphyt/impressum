<?php

declare(strict_types=1);

// The class intentionally mirrors the WordPress core class name and its
// snake_case method names, which do not follow PSR-12.
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps, PSR1.Methods.CamelCapsMethodName.NotCamelCaps
if (!\class_exists('WP_Error')) {
    /**
     * Minimal WP_Error stub for unit tests.
     */
    class WP_Error
    {
        /**
         * @var array<string, string[]>
         */
        private array $errors = [];

        /**
         * @var array<string, mixed>
         */
        private array $error_data = [];

        public function __construct(string $code = '', string $message = '', mixed $data = '')
        {
            if ($code !== '') {
                $this->errors[$code][] = $message;

                if ($data !== '') {
                    $this->error_data[$code] = $data;
                }
            }
        }

        public function get_error_data(string $code = ''): mixed
        {
            if ($code === '') {
                $code = $this->get_error_code();
            }

            return $this->error_data[$code] ?? null;
        }

        public function get_error_code(): string
        {
            return (string) \array_key_first($this->errors);
        }

        public function get_error_message(string $code = ''): string
        {
            if ($code === '') {
                $code = $this->get_error_code();
            }

            return $this->errors[$code][0] ?? '';
        }
    }
}
