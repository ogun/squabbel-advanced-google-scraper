<?php
/**
 * (c) compunect / http://scraping.services 2017
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This source code demonstrates how to use the Scraping.Services API to fully automated scrape a file with keywords.
 * This example will do the following steps:
 * 1. create a job, named like the keyword file.
 * 2. populate the job with keywords from the file (line separated)
 * 3. start scraping the keywords
 * 4. download all pages until the scraping is finished
 *
 * By executing you acknowledge that this example will cause Credit cost on your Scraping.Services account.
 * The price depends on the number of keywords and the selected priority.
 * Also each API call comes at a small cost (at the time of writing 0.01 Credits)
 *
 * Requirements and recommendations:
 * PHP 5 or higher with libcurl support
 * A Linux environment is highly recommended, this script is intended to be run from command line (php api_scrape_google_search.php)
 */

require_once 'ApiSCSE.php';


const API_USER = 'demo@demonstration.com';                        // scraping.services API user account
const API_KEY = '213963b2a7ac8809ef6ec551429fd63f14bd2393';       // scraping.services API credentials
const KEYWORD_FILE = 'fortune-500.txt';                           // file in same folder with keywords to analyze, line by line
const KEYWORD_ARRAY = ['keyword analyzer', 'scrape google', 'apartments in new york']; // alternative keywords (to be loaded in combination of the KEYWORD_FILE contents if it's existent)
const OPERATION_MODE = ApiSCSE::OPERATION_MODES['mode_extended_ideas']; // the autocomplete spidering mode
const SPEED = 5;                                              // The scraping speed (default is 1), 0-100 is possible. However result time and completion at 0 is unpredictable
//const JOBNAME = KEYWORD_FILE;                                   // The name for the job, during this example it's easiest to keep it at the name of the file
$JOBNAME = 'Demo Job';                                            // Fixed identifier for this job
//$JOBNAME = 'demo-' . date('%Y-%M-%D-%H-%i');                   // Dynamic identifier alternative

if (php_sapi_name() == "cli")
{
    define('MODE', 'cli');
} else
{
    define('MODE', 'http');
}

set_time_limit(0);
ini_set("memory_limit", "128M");                 // memory is required only for parsing larger JSON result files
if (MODE == 'http') echo "<pre>";

/**
 * First step - initializing the API
 */
$res = ApiSCSE::init(API_USER, API_KEY, ApiSCSE::JOB_TYPES['scrape_kw_analytics']);
if (!$res)
{
    echo "Error during API initialization: ";
    die(ApiSCSE::getError() . "\n");
}
/**
 * Second step - testing license state [optional]
 */
$license = ApiSCSE::getLicense();
//var_dump($license);
$credits = ApiSCSE::getCredits();
//var_dump($credits);
$jobs = ApiSCSE::getJobs();
//var_dump($jobs);
$jobs_unfinished = 0;
$jobs_finished = 0;
$jobs_running = 0;
foreach ($jobs as $job)
{
    if ($job['progress'] == 100)
    {
        $jobs_finished++;
    } else if ($job['start'] == 1)
    {
        $jobs_running++;
    } else
    {
        $jobs_unfinished++;
    }
}
echo "SCSE license: {$license['name']}\n";
echo "SCSE wallet: {$credits['credit']} Credits\n";
echo "Jobs: $jobs_unfinished unfinished jobs, $jobs_running running jobs, $jobs_finished finished jobs\n";

if ((float)$credits['credit'] < 0.6)
{
    die ("Insufficient Credits in wallet\n");
}
echo "\n";

/**
 * Third step - creating a scrape job, test if job already exists
 */

$job = ApiSCSE::getJob($JOBNAME); // check if the job is already existent
if (!$job)
{
    echo "Creating new job " . $JOBNAME . ": ";
    $job = ApiSCSE::createJob($JOBNAME, null, null, null, SPEED); // language, country and result_count are not relevant for the 'keyword analytics' module
    if ($job)
    {
        echo "success\n";
    } else
    {
        echo("failure\n");
        if (ApiSCSE::getLastResponse()->has_exception) var_dump(ApiSCSE::getLastResponse()->exceptions);
        die(ApiSCSE::getError() . "\n");
    }
}

/**
 * Fourth step - populating the job with keywords, using replace, skip if job is already running
 */

if ($job['start'] == 0)
{
    //$keywords=['one','two','three'];
    if (!file_exists(KEYWORD_FILE) && !count(KEYWORD_ARRAY))
    {
        die("File " . KEYWORD_FILE . " does not exist\n");
    }
    $keywords=[];
    if (file_exists(KEYWORD_FILE))
        $keywords = file(KEYWORD_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $keywords = array_merge($keywords,KEYWORD_ARRAY);
    if (!count($keywords))
    {
        die("No keywords specified\n");
    }
    $res = ApiSCSE::modify_job($JOBNAME, $keywords, ApiSCSE::MODE_REPLACE, null, null, null, SPEED, OPERATION_MODE);
    if (!$res)
    {
        echo "Modification of Job failed\n";
        if (ApiSCSE::getLastResponse()->has_exception) var_dump(ApiSCSE::getLastResponse()->exceptions);
        die();
    }
    echo "Job starting cost: {$res['credit_cost']} Credits\n";
}


/**
 * Fifth step - Starting the scrape job
 */

if ($job['start'] == 0)
{

}
$res = ApiSCSE::start_job($JOBNAME);
if (!$res)
{
    $started = 0;
    foreach (ApiSCSE::getLastResponse()->exceptions as $exception) if ($exception->error_id == 10008)
    {
        $started = 1;
        break;
    }
    if (!$started)
    {
        echo "Job " . $JOBNAME . " could not be started\n";
        die();
    }
    echo "Job " . $JOBNAME . " was already started\n";
} else
{
    echo "Job " . $JOBNAME . " has been started\n";
}


/**
 * Final step - Downloading result pages until job is finished
 * Depending on priority the scraping job will finish sooner or later. The system is capable to scrape many thousands of keywords per minute at high priorities
 * Depending on the current state of the scraping-backend a started scraping job can have a delay of up to 2 minutes before first result data is received.
 *  There are advanced techniques to avoid this delay, please contact our customer service in case.
 */

$latest_page = 0;
$job = ApiSCSE::getJob($JOBNAME);

$original_estimated_hours = $job['estimated_hours_left'];
$start_time = time();
$end_time = $start_time + $original_estimated_hours * 3600 * 3; // 3 times the total estimation timeout
$once = 0; //  always allow one single run to read out finished jobs
while (!$once++ || (time() < $end_time))
{
    echo "Waiting for results, estimated time left: {$job['estimated_hours_left']} ; Progress: {$job['progress']}%..\n";
    if (MODE == 'http') flush();
    sleep(2);
    $job = ApiSCSE::getJob($JOBNAME);
    $total_pages = $job['total_pages'];
    $result_pages = $job['pages'];
    while ($result_pages > $latest_page)
    {
        echo "Downloading new result page " . ($latest_page + 1) . "\n";
        $results = ApiSCSE::getResults($JOBNAME, $latest_page + 1,['get_all'=>true]); // page_quantity is another optional parameter to increase keywords per page, pages_available in get_results response will adapt
        foreach ($results['keyword_results'] as $keyword => $analytic)
        {
            echo "$keyword:\n";
            echo "\tSearch volume average broad: {$analytic['search_volume']['monthly_average']}\n";
            echo "\tSearch volume average exact: {$analytic['search_volume']['monthly_average_exactmatch']}\n";
            echo "\tWebsite density: {$analytic['website_volume']['description']}\n";
            echo "\tNumber of relevant countries: ".count($analytic['countries'])."\n";
            echo "\tNumber of top trending keywords: ".count($analytic['trending']['top'])."\n";
            echo "\tNumber of rising trending keywords: ".count($analytic['trending']['rising'])."\n";
            echo "\tNumber of keyword ideas: ".count($analytic['keyword_ideas'])."\n";
        }
//        $data_dump = json_encode($results,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);  // alternatively dump the whole array for debugging
//        echo $data_dump;
        $latest_page++;
    }

    if ($total_pages == $latest_page) break;
}

$hours_passed = number_format((time() - $start_time) / 3600, 2);
echo "\n";
echo "Scraping of $latest_page pages finished successfully\n";
echo "Estimated time: $original_estimated_hours hours, Real time: $hours_passed hours\n";

if (($hours_passed != '0.00') && ((time() >= $end_time)))
{
    echo "ERROR: Timeout while waiting for results\n";
}

if (MODE == 'http') echo "</pre>";