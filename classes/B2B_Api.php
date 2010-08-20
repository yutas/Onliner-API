<?php

/**
 * B2B_Api class - пример обертки, для работы с API
 *
 * @package 0.0.1
 * @author Onliner
 */
class B2B_Api
{


    private $access_key;                //ключ доступа к API
    private $price_fields = array(  'cat_id' => false,
                                    'dev_id' => false,
                                    'client_pos_id' => false,
                                    'price' => false,
                                    'beznal' => false,
                                    'on_stock' => false,
                                    'comment' => false,
                                    'warranty' => false,
                                    'shipment' => false,
                                    'delete' => false,
                                );
    private $api_url = "http://api.onliner.by/b2b/";
    private $curl;                      //объект curl
    private $curl_timeout = 10;         //время ожидания ответа сервера API
    private $error_msg;
    private $https = FALSE;             //API работает через https
    private $pre_cleaning = FALSE;      //удаляем все позиции перед импортом, или нет
    private $error_codes = array(   '1' => 'Раздел не подключен',
                                    '2' => 'В категории нет девайса с таким кодом',
                                    '3' => 'Превышен лимит предложений на девайс',
                                    '4' => 'Неверный код категории');


    /**
     * Конструктор инициализирует CURL и необходимые параметры
     *
     * @param <type> $_client_login
     * @param <type> $_client_password
     */
    public function  __construct($_client_login = false,$_client_password = false)
    {
        
         //инициализируем CURL
         $this->curl = curl_init();
         //время ожидания ответа сервера API
         curl_setopt($this->curl,CURLOPT_TIMEOUT,$this->curl_timeout);
         //CURL будет возвращать ответ сервера, или FALSE в случае неудачи
         curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,TRUE);
        
        
        if($_client_login && $_client_password)
        {
            return $this->new_session($_client_login, $_client_password);
        }
    }

    /**
     * функция задает адрес api
     *
     * @param string $_api_url
     */
    public function set_api_url($_api_url = false)
    {
        if($_api_url)
        {
            $this->api_url = trim($_api_url);
        }
    }


    /**
     * Чтобы очистить весь прайслист перед импортом нового
     *
     * @param <type> $_pre_cleaning
     */
    public function set_pre_cleaning()
    {
        $this->pre_cleaning = TRUE;
    }

    /**
     * использовать POST при запросах
     */
    public function set_use_post()
    {
        $this->use_post = TRUE;
    }

    /**
     * функция начинает новую сессию с АПИ
     *
     * @param <type> $_cleaning
     * @return <type>
     */
    public function new_session($_login = false, $_password = false)
    {
        if($_login && $_password)
        {
                //урл для начала новой сессии
                $response = $this->process_response(
                                    $this->make_request(
                                                    '',
                                                    array(  
                                                        'login' => $_login,
                                                        'password' => $_password
                                                        )
                                                     )
                                                );
                                                
               if($response)    //при успешной авторизации получаем код доступа сессии
               {
                $this->access_key = $response;
                return $this;
               }
        }
    }
    
    
    /**
     * функция редактирования позиции прайслиста
     *
     * @param <type> $_pos_id - уникальный номер позиции в прайсе
     * @param <type> $_params - набор параметров для изменения 
     * @return <type>
     */
    public function insert_position($_cat_id = false, $_dev_id = false, $_params = array())
    {
        //не отправляем запрос на сервер впустую
        if($_cat_id && $_dev_id && count($_params))
        {
            //фильтруем параметры, оставляя только нужные
            $_params = array_intersect_key($_params,$this->price_fields);
                
             //делаем запрос
            return $this->process_response(
                        $this->make_request('import/position/'.$_cat_id.'/'.$_dev_id.'/', $_params)
                                        );
                
        }
    }

    /**
     * функция редактирования позиции прайслиста
     *
     * @param <type> $_pos_id - уникальный номер позиции в прайсе
     * @param <type> $_params - набор параметров для изменения 
     * @return <type>
     */
    public function edit_position($_cat_id = false, $_dev_id = false, $_pos_id = false, $_params = array())
    {
        //не отправляем запрос на сервер впустую
        if($_cat_id && $_dev_id && $_pos_id && count($_params))
        {
            //фильтруем параметры, оставляя только нужные
            $_params = array_intersect_key($_params,$this->price_fields);
        
            //делаем запрос
            return $this->process_response(
                            $this->make_request('import/position/'.$_cat_id.'/'.$_dev_id.'/'.$_pos_id.'/', $_params)
                                            );
            
        }
    }

    /**
     * функция служит для применения всех изменений прайслиста в текущей сессии.
     * Если после редактирования позиций вызвать эту функцию, то все изменения
     * попадут в очередь импорта и с течением времени будут применены на сервере.
     */
    public function commit($_clean_price = false)
    {
        if($_clean_price == true || $_clean_price == false)
        {
            return $this->process_response(
                        $this->make_request('/commit',array('cleaning' => $_clean_price))
                        );
        }   
    }

    /**
     * функция совершает запрос по выбранному адресу и возвращает ответ
     * 
     * @param string $_uri
     * @param array $_params
     * @param string $_request_method - отправлять данные методом (GET по умолчанию)
     * @return <type>
     */
    public function make_request($_uri = false, $_params = array(), $_request_method = 'GET')
    {
        if($_uri)
        {
            $url = $this->api_url.'key:'.$this->access_key.'/'.trim($_uri.'/');
        }
        else 
        {
            $url = $this->api_url;
        }
        

        
        switch ($_request_method) {
            case 'POST':
                    
                    curl_setopt($this->curl,CURLOPT_POST,count($_params));
                    curl_setopt($this->curl,CURLOPT_POSTFIELDS,http_build_query($_params));
                    curl_setopt($this->curl,CURLOPT_URL,$url);
        
                break;
        
            //по умолчанию отправлять все через GET
            default:
            
                if(!empty($_params))
                {
                    $url .= '?'.http_build_query($_params);
                }
                curl_setopt($this->curl,CURLOPT_URL,$url);
                break;
        }

            $response = curl_exec($this->curl);
            $response_status = curl_getinfo($this->curl,CURLINFO_HTTP_CODE);
            
        return array(
                    'error' => ($response_status == 200)?0:1,
                    'response' => json_decode($response,TRUE)
                    );
    }

    /**
     * функция возвращает статус товаров по заданным pos_id
     * статус 0 означает, что позиция была добавлена без ошибок
     * если функция вернула FALSE и запрос прошел без ошибок - это значит, что
     * все позиции прайслиста были обновлены без ошибок
     */
    public function get_pricelist_report(array $_pos_ids = array())
    {
        $params = array();
        if( ! empty($_pos_ids))
        {
            $params['pos_ids'] = json_encode($_pos_ids);
        }
        $response = $this->process_response($this->make_request('import/report', $params));
        return !$response?FALSE:$this->get_import_errors($response);
    }

    /**
     * Возвращает расшифровку ошибок импорта в отчете
     *
     * @param array $_report
     * @return array
     */
    private function get_import_errors($_report)
    {
        foreach($_report as &$error_code)
        {
            $error_code = $this->error_codes[$error_code];
        }
        return $_report;
    }

    /**
     * функция возвращает код доступа текущей сессии
     */
    public function get_access_key()
    {
        return $this->access_key;
    }

    /**
     * функция устанавливает код доступа
     */
    public function set_access_key($_access_key)
    {
        $this->access_key = $_access_key;
    }

    /**
     * функция возвращает сообщение об ошибке
     */
    public function get_error_msg()
    {
        return $this->error_msg;
    }


    private function process_response($response)
    {
        //если запрос прошел без ошибок, получаем pos_id в качестве ответа
        if(0 == $response['error']) 
        {
            return $response['response'];
        }
        else
        {
            $this->error_msg = $response['response'];
            return FALSE;
        }
    }

    /******************************************************************
     *      Экспортирование позиций прайса, по ответ в json
     ******************************************************************/
    
    public function export_price($_cat_id = false, $mfr_id = false, $_dev_id = false)
    {
        
        return $this->process_response(
                            $this->make_request(
                                        'export/positions'
                                        .($_cat_id?'/'.$_cat_id:'')
                                        .($mfr_id?'/'.$mfr_id:'')
                                        .($_dev_id?'/'.$_dev_id:'').'.json' 
                                                )
                                    );

        
    }


    /*********************************************************
     *      ФУНКЦИИ ДЛЯ ПОЛУЧЕНИЯ СПРАВОЧНОЙ ИНФОРМАЦИИ
     *********************************************************/

    /**
     * Получаем айдишники и названия всех активных разделов каталога
     */
    public function get_active_categories()
    {
        return $this->process_response(
                        $this->make_request('catalog')
                                        );
    }

    /**
     * Получаем айдишники и названия всех подключенных разделов каталога
     */
    public function get_enabled_categories()
    {
        return $this->process_response(
                        $this->make_request('catalog/enabled')
                                        );
    }

    /**
     * Получаем айдишники и названия всех НЕподключенных разделов каталога
     */
    public function get_disabled_categories()
    {
        return $this->process_response(
                            $this->make_request('catalog/disabled')
                                        );
    }

    /**
     * Получаем информацию по разделам каталога и девайсам
     */
    public function get_info($_cat_id = FALSE,$_dev_id = FALSE)
    {
        return $this->process_response(
                            $this->make_request(
                                        'catalog/info/'
                                        .($_cat_id?$_cat_id.'/':'')
                                        .($_dev_id?$_dev_id.'/':'') 
                                                )
                                    );
    }
    
    /**
     * Ищем айдишники девайсов по названию (или его части)
     */
    public function search_device($_search = FALSE, $_cat_id = FALSE, $_mfr_id = FALSE)
    {
        if($_search)
        {
            return $this->process_response(
                                        $this->make_request('catalog/searchid/'
                                                                .($_cat_id?$_cat_id.'/':'')
                                                                .($_mfr_id?$_mfr_id.'/':''),
                                                                    array('q' => $_search)
                                                            )
                                                );
        }                                   
    }

    /**
     * Ищем айдишники категорий по названию (или его части)
     */
    public function search_category($_search = FALSE)
    {
        if($_search)
        {
            return $this->process_response(
                            $this->make_request('catalog/searchcatid/',array('q' => $_search))
                                            );
        }
    }


    /**
     * Получаем список всех производителей в выбранном разделе каталога
     */
    public function get_vendors($_cat_id = FALSE)
    {
        return $this->process_response(
                            $this->make_request(
                                    'catalog/vendors/'
                                    .($_cat_id?$_cat_id.'/':'')
                                                )
                                    );
    }
}
