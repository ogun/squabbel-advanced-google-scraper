<?php

/**
 * (c) compunect / https://scraping.services 2017
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * Requirements:
 * A user account with a positive amount of credits at http://scraping.services
 * PHP 5 or higher with libcurl
 *
 * Usage:
 * This class is used as a PHP static class, it does not need to be constructed but it requires to be initialized.
 * ApiSCSE::init($email,$api_credentials)
 */
class ApiException
{
    public $exception_text;
    /** @var  string $exception_text */
    public $level;
    /** @var  int $level */
    public $unixtime;
    /** @var  int $unixtime */
    public $error_id;
    /** @var  int $error_id */
}

class ApiResponse
{
    public $answer = [];
    /** @var array $answer */
    public $exceptions = [];
    /** @var ApiException $exceptions [] */
    public $has_exception = false;
    /** @var bool $exception */
}

class ApiSCSE
{
    const MODE_REPLACE = 'replace';
    const MODE_APPEND = 'append';
    const JOB_TYPES = ['scrape_google_search'=>'scrape_google_search', 'scrape_bing_search'=>'scrape_bing_search', 'website_tracker'=>'website_tracker', 'scrape_kw_analytics'=>'scrape_kw_analytics'];
    const OPERATION_MODES = ['mode_only_searchvolume'=>'mode_only_searchvolume','mode_normal_analysis'=>'mode_normal_analysis','mode_recurse_ideas'=>'mode_recurse_ideas','mode_extended_ideas'=>'mode_extended_ideas','mode_related_ideas'=>'mode_related_ideas','mode_recurse_trend_ideas'=>'mode_recurse_trend_ideas'];
    public static $API_COUNTRIES = ['Default', 'Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium',
        'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Cayman Islands', 'Central African 
    Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Congo, the Democratic Republic of the', 'Cook Islands', 'Costa Rica', 'Cote D\'ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica',
        'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard Island and Mcdonald Islands', 'Holy See (Vatican City State)', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran, Islamic Republic of', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea, Democratic People\'s Republic of', 'Korea, Republic of', 'Kuwait', 'Kyrgyzstan', 'Lao People\'s Democratic Republic', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macao', 'Macedonia, the Former Yugosalv Republic of', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia, Federated States of', 'Moldova, Republic of', 'Monaco', 'Mongolia', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestinian Territory, Occupied', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda', 'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Pierre and Miquelon', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia and Montenegro', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia and the South Sandwich Islands', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Svalbard and Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan, Province of China', 'Tajikistan', 'Tanzania, United Republic of', 'Thailand', 'Timor-Leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Venezuela', 'Viet Nam', 'Virgin Islands, British', 'Virgin Islands, U.S.', 'Wallis and Futuna', 'Western Sahara', 'Yemen', 'Zambia', 'zw'];
    public static $API_LANGUAGES = ['Afrikaans', 'Albanian', 'Amharic', 'Arabic', 'Armenian', 'Azerbaijani', 'Basque', 'Belarusian', 'Bengali', 'Bihari', 'Bosnian', 'Breton', 'Bulgarian', 'Cambodian', 'Catalan', 'Chinese (Simplified)', 'Chinese (Traditional)', 'Corsican', 'Croatian', 'Czech', 'Danish', 'Dutch', 'English', 'Esperanto', 'Estonian', 'Faroese', 'Filipino', 'Finnish', 'French', 'Frisian', 'Galician', 'Georgian', 'German', 'Greek', 'Guarani', 'Gujarati', 'Hausa', 'Hebrew', 'Hindi', 'Hungarian', 'Icelandic', 'Indonesian', 'Interlingua', 'Irish', 'Italian', 'Japanese', 'Javanese', 'Kannada', 'Kazakh', 'Kinyarwanda', 'Kirundi', 'Korean', 'Kurdish', 'Kyrgyz', 'Laothian', 'Latin', 'Latvian', 'Lingala', 'Lithuanian', 'Macedonian', 'Malagasy', 'Malay', 'Malayalam', 'Maltese', 'Maori', 'Marathi', 'Moldavian', 'Mongolian', 'Montenegrin', 'Nepali', 'Norwegian', 'Norwegian (Nynorsk)', 'Occitan', 'Oriya', 'Oromo', 'Pashto', 'Persian', 'Polish', 'Portuguese (Brazil)', 'Portuguese (Portugal)', 'Punjabi', 'Quechua', 'Romanian', 'Romansh', 'Russian', 'Scots Gaelic', 'Serbian', 'Serbo-Croatian', 'Sesotho', 'Shona', 'Sindhi', 'Sinhalese', 'Slovak', 'Slovenian', 'Somali', 'Spanish', 'Sundanese', 'Swahili', 'Swedish', 'Tajik', 'Tamil', 'Tatar', 'Telugu', 'Thai', 'Tigrinya', 'Tonga', 'Turkish', 'Turkmen', 'Twi', 'Uighur', 'Ukrainian', 'Urdu', 'Uzbek', 'Vietnamese', 'Welsh', 'Xhosa', 'Yiddish', 'Yoruba', 'Zulu'];

    const VERSION = 106;
    private static $api_login = null;
    private static $api_key = null;
    private static $initialized = false;
    private static $error_msg = null;
    private static $job_type = false;

    private static $license = [];
    /** @var ApiResponse $last_response */
    private static $last_response = null;

    /**
     * @param string $login user email/login
     * @param string $key   API credentials
     *
     * @param string $job_type The scraping module to use (see ApiSCSI::JOB_TYPES array)
     *
     * @return bool
     */
    public static function init($login, $key, $job_type='scrape_google_search')
    {
        self::$api_key = $key;
        self::$api_login = $login;
        self::$initialized = true;
        $ret = self::call('info', 'license_info');
        if ($ret === false)
        {
            return false;
        }
        if ($ret->has_exception)
        {
            self::$error_msg = $ret->exceptions[0]->exception_text;

            return false;
        }
        self::$license = $ret->answer['license'];
        if (!in_array($job_type, self::JOB_TYPES))
        {
            self::$error_msg = 'Illegal job_type specified';
            return false;
        }

        self::$job_type = $job_type;

        return true;
    }

    /**
     * Returns the last API call response object
     * @return ApiResponse
     */
    public static function getLastResponse()
    {
        return self::$last_response;
    }


    /**
     *
     * @return array | bool
     */
    public static function getLicense()
    {
        if (!self::$initialized)
        {
            self::$error_msg = 'not initialized';

            return false;
        }

        return self::$license;
    }

    /**
     * @return mixed
     */
    public static function getCredits()
    {
        $res = self::call('info', 'wallet_info');

        return $res->answer['wallet'];
    }

    /**
     * @return mixed
     */
    public static function getJobs()
    {
        $res = self::call(self::$job_type, 'get_jobs');

        return $res->answer['jobs'];
    }

    /**
     * @param $jobname
     *
     * @return bool|mixed
     */
    public static function getJob($jobname)
    {
        $res = self::call(self::$job_type, 'get_job', ['jobname' => $jobname]);
        if ($res->has_exception) return false; // Exception ID 10013 if job does not exist
        return $res->answer['job'];
    }

    /**
     * @param       $jobname
     * @param int   $page
     * @param array $meta optional additional parameters (get_all, page_quantity, etc)
     *
     * @return bool|mixed
     */
    public static function getResults($jobname, $page = 1, $meta=[])
    {
        $parameters=['jobname' => $jobname, 'page' => $page];
        $parameters = array_merge($parameters,$meta);
        $res = self::call(self::$job_type, 'get_results', $parameters);
        if ($res->has_exception) return false; // Exception ID 10013 if job does not exist
        return $res->answer['results'];
    }

    public static function start_job($jobname)
    {
        $res = self::call(self::$job_type, 'start_job', ['jobname' => $jobname]);
        if ($res->has_exception) return false;

        return $res->answer['job'];
    }

    public static function remove_job($jobname)
    {
        $res = self::call(self::$job_type, 'remove_job', ['jobname' => $jobname]);
        if ($res->has_exception) return false;

        return $res->answer[0];
    }

    /**
     * @param        $jobname
     * @param string $language
     * @param string $country
     * @param int    $results_per_kw
     * @param int    $priority

     * @return bool|mixed
     */
    public static function createJob($jobname, $language = 'English', $country = 'Default', $results_per_kw = 100, $priority = 1)
    {
        self::$error_msg=null;
        if (self::$job_type !== self::JOB_TYPES['scrape_kw_analytics'])
        {
            if (!in_array($language, self::$API_LANGUAGES))
            {
                self::$error_msg="Invalid language";
                return false;
            }
            if (!in_array($country, self::$API_COUNTRIES))
            {
                self::$error_msg="Invalid country";
                return false;
            }
            if ($results_per_kw < 1)
            {
                self::$error_msg="Invalid results_per_kw";
                return false;
            }
            if ($results_per_kw > 1000)
            {
                self::$error_msg="Invalid results_per_kw";
                return false;
            }
        }
        if ($priority < 0)
        {
            self::$error_msg="Invalid priority";
            return false;
        }
        if ($priority > 100)
        {
            self::$error_msg="Invalid priority";
            return false;
        }


        $res = self::call(self::$job_type, 'create_job', ['jobname' => $jobname, 'language' => $language, 'country' => $country, 'job_type' => self::$job_type, 'max_results' => $results_per_kw, 'priority' => $priority]);
        if ($res->has_exception) return false;

        return $res->answer['job'];
    }


    /**
     * @param        $jobname
     * @param array  $keywords UTF8 encoded
     * @param string $keyword_operation_mode
     * @param null|string   $language
     * @param null|string   $country
     * @param null|int   $results_per_kw
     * @param null|int   $priority
     *
     * @param null|string   $operation_mode optional (keyword analytics module), see ApiSCSE::OPERATION_MODES
     *
     * @return bool|mixed
     */
    public static function modify_job($jobname, $keywords = [], $keyword_operation_mode = self::MODE_APPEND, $language = null, $country = null, $results_per_kw = null, $priority = null, $operation_mode = null)
    {
        self::$error_msg=null;
        if (isset($language))
        {
            if (!in_array($language, self::$API_LANGUAGES))
            {
                self::$error_msg="Invalid language";
                return false;
            }
        }
        if (isset($country))
        {
            if (!in_array($country, self::$API_COUNTRIES))
            {
                self::$error_msg="Invalid country";
                return false;
            }
        }

        if (isset($priority))
        {
            if ($priority < 0)
            {
                self::$error_msg="Invalid priority";
                return false;
            }
            if ($priority > 100)
            {
                self::$error_msg="Invalid priority";
                return false;
            }
        }
        if (isset($results_per_kw))
        {
            if ($results_per_kw < 1)
            {
                self::$error_msg="Invalid results_per_kw";
                return false;
            }
            if ($results_per_kw > 1000)
            {
                self::$error_msg="Invalid results_per_kw";
                return false;
            }
        }

        if (isset($operation_mode))
        {
            if (!in_array($operation_mode, self::OPERATION_MODES))
            {
                self::$error_msg = 'Illegal operation_mode specified';
                return false;
            }
        }


        $object = ['jobname' => $jobname];
        if (count($keywords))
        {
            $object['keyword_operation'] = $keyword_operation_mode;
            $object['keywords'] = $keywords;
        }
        if (isset($results_per_kw)) $object['max_results'] = (int)$results_per_kw;
        if (isset($language)) $object['language'] = $language;
        if (isset($country)) $object['country'] = $country;
        if (isset($priority)) $object['priority'] = (int)$priority;
        if (isset($operation_mode)) $object['operation_mode'] = $operation_mode;

        $res = self::call(self::$job_type, 'modify_job', $object);
        if ($res->has_exception)
        {
            self::$error_msg="API exception";
            return false;
        }

        return $res->answer['job'];
    }


    /**
     * Should be called after each failed API call
     * Return false in case of no error
     * Return error message in case of error
     * @return bool|string
     */
    public static function getError()
    {
        return self::$error_msg ? self::$error_msg : false;
    }


    /**
     * The call() function will either return an object similar to ApiResponse or false.
     * false is returned only on hard errors, for example a network connection issue or false data received
     *
     * @param string $job_type
     * @param string $task
     * @param array  $arguments
     *
     * @return bool | ApiResponse
     */
    public static function call($job_type, $task, $arguments = [])
    {
        if (!self::$initialized)
        {
            self::$error_msg = 'not initialized';
            return false;
        }
        self::$error_msg = null;
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl_handle, CURLOPT_TIMEOUT, 120);
        if (count($arguments))
            curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, "ApiSCSE;" . self::VERSION);
        curl_setopt($curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        if (count($arguments))
        {
            $api_object = json_encode($arguments);
            curl_setopt($curl_handle, CURLOPT_POST, true);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, ['json' => $api_object]);
        }

        $url = "https://scraping.services/api?user=" . self::$api_login . "&credentials=" . self::$api_key . "&V=" . self::VERSION . "&type=json&job_type=$job_type&task=$task";
        curl_setopt($curl_handle, CURLOPT_URL, $url);

        $data = curl_exec($curl_handle);
        if ($data === false)
        {
            self::$error_msg = 'Network connection error';

            return false;
        }
        curl_close($curl_handle);
//        var_dump($data);
        $ret = json_decode($data, true);
        if ($ret)
        {
            $response = new ApiResponse();
            $response->answer = $ret['answer'];
            foreach ($ret['exceptions'] as $exception)
            {
                $response->exceptions[] = (object)$exception;
                $response->has_exception = true;
            }
            self::$last_response = $response;

            return $response;
        }
        if (strstr($data,'simultaneous access'))
            self::$error_msg = 'Error: simultanous access locking error';
        else
            self::$error_msg = 'Invalid or no information received';

        return false;
    }


}