<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Response to any AJAX requests to the app.
 *
 * An AJAX response is always JSON, and always has this structure:
 * {
 *    result: {
 *       statusCode: {int},
 *       status: {string},
 *       message: {string}
 *    },
 *    payload: {any}
 * }
 *
 * The result structure enables the receiver to determine the success or otherwise of the request. The payload content
 * should be defined by the producer of the response, and can be any valid JSON. By default, the payload is an empty
 * object.
 */
class AjaxResponse extends JsonResponse
{
    /**
     * Status code indicating success.
     */
    public const StatusCodeOk = 0;

    /**
     * Status code indicating an error.
     */
    public const StatusCodeError = 1;

    /**
     * The first status code that you can use for your own statuses.
     */
    public const StatusCodeUserBase = 500;

    /**
     * Status indicating success.
     */
    public const StatusOk = "OK";

    /**
     * Status indicating an error.
     */
    public const StatusError = "Error";

    /**
     * Initialise a new AjaxResponse.
     *
     * The payload can be any valid JSON content. It defaults to an empty object. PHP Associative arrays will be
     * converted to JSON objects. If you want a normal array to be sent as a JSON object you need to convert it to an
     * object first (e.g. (object) ["fizz", "buzz",]).
     *
     * @param int $statusCode A code inidicating the status. OK (0) and Error (1) are defined as standard codes, but
     * your requests may implement others they require using codes from StatusCodeUserBase and above.
     * @param string $status A string indicating the status. OK and Error are defined as standard statuses, but your
     * requests may implement any others they require.
     * @param string $message A short text message to accompany the status (e.g. "User login successful.", "Barcode
     * saved successfully/", "Your username/password was not recognised.").
     * @param array|null $payload The payload to send.
     */
    public function __construct(int $statusCode, string $status, string $message = "", array $payload = null)
    {
        parent::__construct([
            "result" => compact("statusCode", "status", "message"),
            "payload" => $payload ?? (object) [],
        ]);
    }
}
