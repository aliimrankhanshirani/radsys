<?php


    const RIG_VERSION = 2.2;

    require_once '../includes/core/mvc.php';
    require_once '../includes/core/orm.php';
    require_once '../includes/settings.php';
    require_once '../includes/init.php';


    print "<pre><b>radSYS - rig  v".RIG_VERSION."</b>\n\n";


    $rest_settings = "<?php
    /*
     * radSYS - RIG - RESTFul Interface Generator v".RIG_VERSION."
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

    ";
    file_put_contents('../includes/settings_restful.php', $rest_settings);
    print "1. restful settings file created.\n";

    $rest_layout_html = '<area:REST/>';
    file_put_contents('../application/views/layouts/api_restful.html', $rest_layout_html);
    print "2. restful layout file created.\n";


    /***********************************************
     *
     *   RESTFul API controller creation
     *
     */

    $schema = DB_NAME;//@$argv[1];
    require_once 'rad_retinfo.php';
    $tables = db_get_tables($schema);

    $case_code = '';
    foreach ($tables as $table)
    {
        $model = table_to_model_name($table);
        $case_code .="
                case '$table':
                    \$model = new $model(\$primary_id);
                    break;

        ";
    }

    //projects/1/modules/files/115

    $rest_controller = "<?php
    /*
     * radSYS - RIG - RESTFul Interface Generator v".RIG_VERSION."
     * NOTE : do not modify this file! As it gets overwritten automatically
     * @author rig <radsys.com/doc>
     * @date ".date('F d, Y h:i:sA', time())."
     */

    require_once '../includes/settings_restful.php';

    class api extends ApiController
    {
        public  \$layout = 'api_restful';
        private \$entities = Array('".implode("','", $tables)."');
        public  \$map = Array();
        public  \$hierarchical_data = Array();

        public function __call(\$entity, \$args)
        {
            if (!in_array(\$entity, \$this->entities))
            {
                \$this->rest_format_data(
                    FALSE,
                    RESTFUL_NOTFOUND,
                    'Not found'
                );
                return;
            }

            \$model_name = \$this->entity_to_model(\$entity);
            \$model = new \$model_name;

            if (
                \$this->SYS->request_method == 'GET' OR
                \$this->SYS->request_method == 'DELETE' OR
                \$this->SYS->request_method == 'PUT'
            )
            {
                if (!empty(\$this->ARGS))
                {
                    if (!is_numeric(\$this->ARGS[count(\$this->ARGS)-1]))
                        \$GLOBALS['RestLastEntity'] = \$this->ARGS[count(\$this->ARGS)-1];
                }
                else
                    \$GLOBALS['RestLastEntity'] = \$entity;

                if (!in_array(\$this->return, Array('normal','hierarchical')))
                {
                    \$this->rest_format_data(
                        FALSE,
                        RESTFUL_BADREQUEST,
                        'return data type not understood - '.\$this->return.'. Valid are hierarchical and normal [normal is default]'
                    );
                    return NULL;
                }



                \$map = Array(\$entity);
                \$args = func_get_args();
                \$args = \$args[1];



                if (\$this->return == 'hierarchical')
                    \$last_hierarchical_object = &\$this->hierarchical_data;

                if (empty(\$args))
                {
                    /** return all records **/
                    \$model->find();
                    if (\$this->return == 'hierarchical')
                    {
                        \$this->hierarchical_data [\$entity] = \$model->get_data();
                        \$last_hierarchical_object = &\$this->hierarchical_data [\$entity];
                    }
                }
                else
                {
                    foreach (\$args as \$ka => \$arg)
                    {
                        if (!is_numeric(\$arg))
                        {
                            /** reference entity not found */
                            if (!\$model->is_mapable(\$arg))
                            {
                                \$this->rest_format_data(
                                    FALSE,
                                    RESTFUL_BADREQUEST,
                                    'Bad request'
                                );
                                return;
                            }

                            if (\$model->count == 0)
                            {
                                \$this->rest_format_data(
                                    FALSE,
                                    RESTFUL_NOTFOUND,
                                    'Resource not found'
                                );
                                return;
                            }
                            //move to next mapable
                            \$model = \$model->{\$arg};
                            if (\$this->return == 'hierarchical')
                            {
                                \$last_hierarchical_object[\$arg] = \$model->get_data();
                                \$last_hierarchical_object = &\$last_hierarchical_object[\$arg][0];
                            }
                        }
                        else
                        if (is_numeric(\$arg) && intval(\$arg) > 0)
                        {
                            /** is numeric **/
                            \$map[] = \$model->table_name;
                            \$model
                                ->clear()
                                ->limit(1)
                                ->find(\"`\$model->table_key`=\".intval(\$arg))
                            ;
                            if (\$ka == 0 && \$this->return == 'hierarchical')
                            {
                                \$this->hierarchical_data [\$entity] = \$model->get_data();
                                \$last_hierarchical_object = &\$this->hierarchical_data [\$entity][0];
                            }

                        }
                        else //is numeric and is <= 0
                        {
                            \$this->rest_format_data(
                                FALSE,
                                RESTFUL_BADREQUEST,
                                'Bad request'
                            );
                            return;
                        }
                    }
                }

                if (\$model->count == 0)
                {
                    \$this->rest_format_data(
                        FALSE,
                        RESTFUL_NOTFOUND,
                        'Resource not found'
                    );
                    return;
                }

                if (\$this->SYS->request_method == 'GET')
                {
                    \$data = \$this->return == 'hierarchical'
                                ? \$this->hierarchical_data
                                : \$model->get_data();

                    \$this->rest_format_data(
                        TRUE,
                        \$data
                    );
                }
                else
                if (\$this->SYS->request_method == 'DELETE')
                {
                    \$ids = \$model->selected_keys;
                    \$model->delete_all();

                    \$this->rest_format_data(
                        TRUE,
                        Array(
                            'message' => 'Record(s) deleted from entity '.\$model_name.' successfully',
                            'ids' => \$ids,
                        )
                    );
                }
                else
                if (\$this->SYS->request_method == 'PUT')
                {
                    \$report = \$model->eval_data(\$_POST);

                    if (!empty(\$report['errors']))
                    {
                        \$this->rest_format_data(
                            FALSE,
                            RESTFUL_BADREQUEST,
                            Array(
                                'message' => 'Bad request - invalid or insufficient data. Check details',
                                'details' => \$report['errors'],
                            )
                        );
                        return;
                    }
                    else
                    {
                        foreach (\$report['ok'] as \$field => \$value)
                            \$model->{\$field} = \$value;

                        \$model->save();

                        \$this->rest_format_data(
                            TRUE,
                            Array(
                                'message' => 'Record updated in entity '.\$model_name.' successfully',
                                'id' => \$model->{\$model->table_key}
                            )
                        );
                    }



                }
            }
            else
            if (\$this->SYS->request_method == 'POST')
            {
                if (!empty(\$this->ARGS))
                {
                    \$this->rest_format_data(
                        FALSE,
                        RESTFUL_BADREQUEST,
                        'Invalid post request. Use single entity'
                    );
                    return;
                }

                \$report = \$model->eval_data(\$_POST);

                if (!empty(\$report['errors']))
                {
                    \$this->rest_format_data(
                        FALSE,
                        RESTFUL_BADREQUEST,
                        Array(
                            'message' => 'Bad request - invalid or insufficient data. Check details',
                            'details' => \$report['errors'],
                        )
                    );
                    return;
                }
                else
                {
                    \$model->add(\$report['ok']);

                    if (\$model->count > 0)
                    {
                        \$this->rest_format_data(
                            TRUE,
                            Array(
                                'status' => RESTFUL_CREATED,
                                'message' => 'Record added to '.\$model_name,
                                'id' => \$model->{\$model->table_key},
                            )
                        );
                    }
                    else
                    {
                        /** critical error has occured **/
                        \$this->rest_format_data(
                            FALSE,
                            RESTFUL_UNSUPPORTEDENTITY,
                            'Unprocessible entity - some system level error has occured'
                        );
                        return;
                    }
                }
            }
            else
            {
                /** no extramethods **/
                \$this->rest_format_data(
                    FALSE,
                    RESTFUL_NOTALLOWED,
                    'Method not allowed - allowed are : GET, POST, PUT/UPDATE & DELETE'
                );
                return;
            }
        }
    }



    ";
    file_put_contents('../application/controllers/api.php', $rest_controller);
    print "3. restful controller file created.\n";

    print "Features: GET POST DELETE [X PUT].\n";




    print '</pre>';
