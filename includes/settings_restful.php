<?php
    /*
     * radSYS - RIG - RESTFul Interface Generator v2.2
     * NOTE : do not modify this file! As it gets overwritten automatically
     */

    const RESTFUL_CONTROLLER     = 'api';
    const RESTFUL_LAYOUT         = 'api_restful_layout';
    const RESTFUL_DEFAULT_LIMIT  = 20;

    /**
     * RESTFul responses' constants
     */
    const RESTFUL_OK = '200';// OK - Response to a successful GET, PUT, PATCH or DELETE. Can also be used for a POST that doesn't result in a creation.
    const RESTFUL_CREATED = '201';// Created - Response to a POST that results in a creation. Should be combined with a Location header pointing to the location of the new resource
    const RESTFUL_NOCONTENT = '204';// No Content - Response to a successful request that won't be returning a body (like a DELETE request)
    const RESTFUL_NOTMODIFIED = '304';// Not Modified - Used when HTTP caching headers are in play
    const RESTFUL_BADREQUEST = '400';// Bad Request - The request is malformed, such as if the body does not parse
    const RESTFUL_UNAUTHORIZED = '401';// Unauthorized - When no or invalid authentication details are provided. Also useful to trigger an auth popup if the API is used from a browser
    const RESTFUL_FORBIDDEN = '403';// Forbidden - When authentication succeeded but authenticated user doesn't have access to the resource
    const RESTFUL_NOTFOUND = '404';// Not Found - When a non-existent resource is requested
    const RESTFUL_NOTALLOWED = '405';// Method Not Allowed - When an HTTP method is being requested that isn't allowed for the authenticated user
    const RESTFUL_RESOURCEUNAVAILABLE = '410';// Gone - Indicates that the resource at this end point is no longer available. Useful as a blanket response for old API versions
    const RESTFUL_UNSUPPORTEDMEDIA = '415';// Unsupported Media Type - If incorrect content type was provided as part of the request
    const RESTFUL_UNSUPPORTEDENTITY = '422';// Unprocessable Entity - Used for validation errors
    const RESTFUL_TOOMANYREQUESTS = '429';// Too Many Requests - When a request is rejected due to rate limiting
    const RESTFUL_SERVERERROR = '500';// – Internal Server Error – API developers should avoid this error. If an error occurs in the global catch blog, the stracktrace should be logged and not returned as response.

    