<?php
/**
 * (c) compunect / https://scrapiong.services 2017
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This source code demonstrates how to use the Scraping.Services API to automatically download all available results from a job
 *
 * By executing you acknowledge that this example will cause Credit cost on your Scraping.Services account.
 * The cost at the time of writing is at 0.01 credits per API call made
 *
 * This example may be customized to provide a file format of your choice.
 * This example currently only writes keyword, position, URL, title into the respective files. Data like sitelinks, knowledge or description can be easily added.
 *
 * Requirements and recommendations:
 * PHP 5 or higher with libcurl support, up to date version of ApiSCSE.php must be present in directory
 * A Linux environment is recommended
 */

require_once 'ApiSCSE.php';


const API_USER = 'demo@demonstration.com';                        // scraping.services API user account
const API_KEY = '213963b2a7ac8809ef6ec551429fd63f14bd2393';       // scraping.services API credentials (https://scraping.services/?api)
const DIRECTORY = 'results';                                      // subdirectory for data storage (will be created as subdirectory)
const RESULTS_PER_KEYWORD = 1000;                                 // may be used to reduce the number of data stored in the file (1-1000, 1000 default)
const MODULE = 'scrape_google_search';                            // use 'scrape_google_search' or 'scrape_bing_search'
const OVERWRITE = false;                                          // set to true to force a redownload, set to false to skip existing files/pages.
const SEPARATOR = ';';                                            // separator in file between fields

const JOBNAME = 'Job-name';                                      // the exact name of the job to download

if (php_sapi_name() == "cli")
{
    define('MODE', 'cli');
} else
{
    define('MODE', 'http');
}

set_time_limit(0);
if (MODE == 'http') echo "<pre>";

/**
 * First step - initializing the API
 */
$res = ApiSCSE::init(API_USER, API_KEY, MODULE);
if (!$res)
{
    echo "Error during API initialization: ";
    die(ApiSCSE::getError() . "\n");
}
/**
 * Second step - testing license state
 */
$license = ApiSCSE::getLicense();
$credits = ApiSCSE::getCredits();

echo "SCSE license active: {$license['name']}\n";
echo "SCSE wallet: {$credits['credit']} Credits\n";

if ((float)$credits['credit'] < 0.1)
{
    die ("Insufficient Credits in wallet\n");
}
echo "\n";

$job = ApiSCSE::getJob(JOBNAME);
//var_dump($job);
if ($job == NULL)
{
    die("Job not found in module ".MODULE."\n");
}
if ($job['start'] == 0)
{
    die("Job was not started\n");
}

echo    "Job {$job['jobname']}:\n".
    "\t Created:\t{$job['created']}\n".
    "\t Max results per keyword:\t{$job['max_results']}\n".
    "\t Keywords:\t{$job['total_keywords']}\n".
    "\t Finished:\t{$job['total_results']}\n".
    "\t Available pages\t{$job['pages']}\n".
    "\t Estimated pages\t{$job['total_pages']}\n".
    "\t Finish:\t".($job['finished']?$job['finished']:$job['estimated_hours_left'].' hours')."\n";


@mkdir(DIRECTORY);
for ($page = 1; $page <= $job['pages']; $page++)
{
    $jobname=$job['jobname'];
    $filename_organic=$jobname.'.organic.'.$page;
    $filename_creative=$jobname.'.creative.'.$page;
    if (!OVERWRITE && file_exists(DIRECTORY . '/' . $filename_organic))
    {
        echo "\tskipping page $page, file '$filename_organic' exists\n";
        continue;
    }

    if (!$job['finished'])
    {
        $filename_organic.='.part';
        $filename_creative.='.part';
    }
    $results = ApiSCSE::getResults(JOBNAME, $page);
    @unlink(DIRECTORY.'/'.$filename_organic);
    @unlink(DIRECTORY.'/'.$filename_creative);
    echo "Handling page/file $page\n";
    foreach ($results['keyword_results'] as &$result)
    {
        $position=0;
        foreach ($result['results_organic'] as $list)
        {
            $position++;
            if ($position > RESULTS_PER_KEYWORD) break;     // skip any remaining results

            $line = $result['keyword'].SEPARATOR.$position.SEPARATOR.$list['url'].SEPARATOR.$list['title'].SEPARATOR."\r\n";      // customize format and file content here
            $bytes = file_put_contents(DIRECTORY.'/'.$filename_organic, $line , FILE_APPEND | LOCK_EX);
            if (!$bytes)
            {
                die("Error writing to ".DIRECTORY.'/'.$filename_organic);
            }
        }
        if ($result['creative'] > 0)
        {
            $position=0;
            foreach ($result['results_creative'] as $list)
            {
                $position++;
                if ($position > RESULTS_PER_KEYWORD) break;

                $line = $result['keyword'].SEPARATOR.$position.SEPARATOR.$list['url'].SEPARATOR.$list['title'].SEPARATOR."\r\n";  // customize format and file content here
                $bytes = file_put_contents(DIRECTORY.'/'.$filename_creative, $line , FILE_APPEND | LOCK_EX);
                if (!$bytes)
                {
                    die("Error writing to ".DIRECTORY.'/'.$filename_creative);
                }
            }
        }
    }
}

echo "finished all available pages\n";


if (MODE == 'http') echo "</pre>";