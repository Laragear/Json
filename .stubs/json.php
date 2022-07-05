<?php

namespace Illuminate\Http
{
    class Request
    {
        /**
         * Returns a Json instance from the Request JSON input, or a key value.
         *
         * @param  string|int|null  $key
         * @param  mixed|null  $default
         * @return \Laragear\Json\Json|mixed
         */
        public function getJson(string|int $key = null, mixed $default = null): mixed
        {
            //
        }
    }
}
