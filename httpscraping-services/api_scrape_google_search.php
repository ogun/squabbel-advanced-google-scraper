<?php

/**
 * (c) compunect / http://scraping.services 2016
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


const API_USER = '_EMAIL_';                                       // scraping.services API user account
const API_KEY = '_CREDENTIALS_';                                  // scraping.services API credentials
const KEYWORD_FILE = 'fortune-500.txt';                           // file in same folder with keywords, line by line
const RESULTS_PER_KEYWORD = 100;                                  // number of results to scrape per keywords (default is 100)
const LANGUAGE = 'English';                                       // the language used for scraping, see ApiSCSE::$API_LANGUAGES for possible options (default is 'English')
const COUNTRY = 'Default';                                        // the country used for scraping, see ApiSCSE::$API_COUNTRIES for possible options (default is 'Default')
const PRIORITY = 10;                                              // The scraping speed (default is 1), 0-100 is possible. However result time at 0 is unpredictable
//const JOBNAME = KEYWORD_FILE;                                   // The name for the job, during this example it's easiest to keep it at the name of the file
const JOBNAME = 'Demo Job';                                       // optional constant for the job name
if (php_sapi_name() == "cli") {
    define('MODE', 'cli');
} else {
    define('MODE', 'http');
}

set_time_limit(0);
if (MODE == 'http') echo "<pre>";

/**
 * First step - initializing the API
 */
$res = ApiSCSE::init(API_USER, API_KEY);
if (!$res) {
    echo "Error during API initialization: ";
    die(ApiSCSE::getError() . "\n");
}
/**
 * Second step - testing license state
 */
$license = ApiSCSE::getLicense();
$credits = ApiSCSE::getCredits();
$jobs = ApiSCSE::getJobs();
$jobs_unfinished = 0;
$jobs_finished = 0;
$jobs_running = 0;
foreach ($jobs as $job) {
    if ($job['progress'] == 100) {
        $jobs_finished++;
    } else if ($job['start'] == 1) {
        $jobs_running++;
    } else {
        $jobs_unfinished++;
    }
}
echo "SCSE license active: {$license['name']}\n";
echo "SCSE wallet: {$credits['credit']} Credits\n";
echo "Jobs: $jobs_unfinished unfinished jobs, $jobs_running running jobs, $jobs_finished finished jobs\n";

if ((float) $credits['credit'] < 0.6) {
    die("Insufficient Credits in wallet\n");
}
echo "\n";

/**
 * Third step - creating a scrape job, test if job already exists
 */

$job = ApiSCSE::getJob(JOBNAME);
if (!$job) {
    echo "Creating job " . JOBNAME . ": ";
    $job = ApiSCSE::createJob(JOBNAME, LANGUAGE, COUNTRY, RESULTS_PER_KEYWORD, SPEED);
    if ($job) {
        echo "success\n";
    } else {
        echo ("failure\n");
        if (ApiSCSE::getLastResponse()->has_exception) var_dump(ApiSCSE::getLastResponse()->exceptions);
        die();
    }
}

/**
 * Fourth step - populating the job with keywords, using replace, skip if job is already running
 */

if ($job['start'] == 0) {
    //$keywords=['one','two','three'];
    if (!file_exists(KEYWORD_FILE)) {
        die("File " . KEYWORD_FILE . " does not exist\n");
    }
    $keywords = file(KEYWORD_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $res = ApiSCSE::modify_job(JOBNAME, $keywords, ApiSCSE::MODE_REPLACE, LANGUAGE, COUNTRY, RESULTS_PER_KEYWORD, SPEED);
    if (!$res) {
        echo "Modification of Job failed\n";
        if (ApiSCSE::getLastResponse()->has_exception) var_dump(ApiSCSE::getLastResponse()->exceptions);
        die();
    }
    echo "Job starting cost: {$res['credit_cost']} Credits\n";
}


/**
 * Fifth step - Starting the scrape job
 */

if ($job['start'] == 0) { }
$res = ApiSCSE::start_job(JOBNAME);
if (!$res) {
    $started = 0;
    foreach (ApiSCSE::getLastResponse()->exceptions as $exception) if ($exception->error_id == 10008) {
        $started = 1;
        break;
    }
    if (!$started) {
        echo "Job " . JOBNAME . " could not be started\n";
        die();
    }
    echo "Job " . JOBNAME . " was already started\n";
} else {
    echo "Job " . JOBNAME . " has been started\n";
}


/**
 * Final step - Downloading result pages until job is finished
 * Depending on priority the scraping job will finish sooner or later. The system is capable to scrape many thousands of keywords per minute at high priorities
 * Depending on the current state of the scraping-backend a started scraping job can have a delay of up to 2 minutes before first result data is received.
 *  There are advanced techniques to avoid this delay, please contact our customer service in case.
 */

$latest_page = 0;

$job = ApiSCSE::getJob(JOBNAME);

$original_estimated_hours = $job['estimated_hours_left'];
$start_time = time();
$end_time = $start_time + $original_estimated_hours * 3600 * 3; // 3 times the total estimation timeout
$once = 0; // allow one single run to read out finished jobs
$total_organic = 0;
$total_creative = 0;
while (!$once++ || (time() < $end_time)) {
    echo "Waiting for results, estimated time left: {$job['estimated_hours_left']} hours ; Progress: {$job['progress']}%..\n";
    if (MODE == 'http') flush();
    sleep(5);
    $job = ApiSCSE::getJob(JOBNAME);
    $total_pages = $job['total_pages'];
    $result_pages = $job['pages'];
    while ($result_pages > $latest_page) {
        echo "Downloading new result page " . ($latest_page + 1) . "\n";
        $results = ApiSCSE::getResults(JOBNAME, $latest_page + 1);
        foreach ($results['keyword_results'] as $result) {
            echo "Keyword: \"{$result['keyword']}\" - Organic results: {$result['count_organic']}, Creative results: {$result['count_creative']}\n";
            $total_organic += (int) $result['count_organic'];
            $total_creative += (int) $result['count_creative'];
            echo "Organic results:\n";
            foreach ($result['results_organic'] as $list) {
                echo "\tURL: {$list['url']}\n";
                echo "\tTitle: {$list['title']}\n";
                echo "\tDescription: {$list['description']}\n";
                if (count($list['sitelinks'])) {
                    foreach ($list['sitelinks'] as $sitelink) {
                        echo "\t\tURL: {$sitelink['url']}\n";
                        echo "\t\tTitle: {$sitelink['title']}\n";
                        echo "\t\tDescription: {$sitelink['description']}\n";
                    }
                }
                echo "\n";
            }
            if ($result['creative'] > 0) {
                echo "Advertisements:\n";
                foreach ($result['results_creative'] as $list) {
                    echo "\tURL: {$list['url']}\n";
                    echo "\tTitle: {$list['title']}\n";
                    echo "\tDescription: {$list['description']}\n";
                    if (count($list['sitelinks'])) {
                        foreach ($list['sitelinks'] as $sitelink) {
                            echo "\t\tURL: {$sitelink['url']}\n";
                            echo "\t\tTitle: {$sitelink['title']}\n";
                            echo "\t\tDescription: {$sitelink['description']}\n";
                        }
                    }
                    echo "\n";
                }
            }
            echo "\n";
        }
        $latest_page++;
    }

    if ($total_pages == $latest_page) break;
}

$hours_passed = number_format((time() - $start_time) / 3600, 2);
echo "\n";
echo "Scraping of $latest_page pages finished successfully\n";
echo "Received $total_organic organic results and $total_creative ads\n";
echo "Estimated time: $original_estimated_hours hours, Real time: $hours_passed hours\n";

if (($hours_passed != '0.00') && ((time() >= $end_time))) {
    echo "ERROR: Timeout while waiting for results\n";
}

if (MODE == 'cli') echo "</pre>";
