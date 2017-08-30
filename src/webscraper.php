<?php
namespace CuttingWeb;
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
//use GuzzleHttp\Exception;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector;

/**
 * Created by PhpStorm.
 * User: Ross Edlin
 * Date: 30/08/2017
 * Time: 11:17
 *
 * Class Scraper
 * @package CuttingWeb
 */
class Scraper
{
	private $baseUrl = false;
	private $data    = [];

	/**
	 * @param $author
	 * @param $authorUrl
	 * @param $title
	 * @param $articleUrl
	 */
	private function addItem($author, $authorUrl, $title, $articleUrl)
	{
		$code = self::codify($author);

		/**
		 * Scrap Article Directly
		 */
		$html = $this->getHtmlFromUrl($this->baseUrl, $articleUrl);

		/**
		 * Extract Article Date
		 */
		$articleDate = false;
		$data        = $this->extractHtml($html, '.record .meta .date');
		if (isset($data[0]) && trim($data[0]) != '')
		{
			$articleDate = trim($data[0]);
		}

		$article = [
			'articleTitle' => $title,
			'articleUrl'   => $articleUrl,
			'articleDate'  => $articleDate,
		];

		/**
		 * Second Article
		 */
		if (isset($this->data[$code]))
		{
			$this->data[$code]['authorCount']++;
			$this->data[$code]['articles'][] = $article;
		}

		/**
		 * First Article
		 */
		else
		{
			/**
			 * Scrap the author URL
			 */
			$authorBio           = false;
			$authorTwitterHandle = false;

			$html = $this->getHtmlFromUrl($this->baseUrl, $authorUrl);

			/**
			 * Extract Bio
			 */
			$data = $this->extractHtml($html, '.author-bio .abstract');
			if (isset($data[0]) && trim($data[0]) != '')
			{
				$authorBio = trim($data[0]);
			}

			/**
			 * Extract Twitter Handle
			 */
			$data = $this->extractHtml($html, '.author-bio .abstract a');
			if (isset($data[0]) && trim($data[0]) != '')
			{
				$authorTwitterHandle = trim($data[0]);
			}

			$this->data[$code] = [
				'authorName'          => $author,
				'authorBio'           => $authorBio,
				'authorTwitterHandle' => $authorTwitterHandle,
				'authorCount'         => 1,
				'articles'            => [$article],
			];
		}
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param $html
	 * @param $filter
	 *
	 * @return array
	 */
	private function extractHtml($html, $filter)
	{
		try
		{
			$crawler  = new Crawler($html);
			$articles = $crawler
				->filter($filter)
				->each(function (Crawler $node)
				{
					return $node->html();
				});

			return $articles;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * @param $html
	 * @param $filter
	 *
	 * @return array
	 */
	private function extractHref($html, $filter)
	{
		try
		{
			$crawler = new Crawler($html);
			$link    = $crawler->filter($filter)->attr('href');
			return $link;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * @param $str
	 *
	 * @return string
	 */
	private function cleanAuthor($str)
	{
		return trim($str);
	}

	/**
	 * @param $str
	 *
	 * @return string
	 */
	private function cleanTitle($str)
	{
		return trim($str);
	}

	private function getHtmlFromUrl($baseUrl, $subUrl = '/')
	{
		$client = new Client([
			'base_uri' => $baseUrl,
			'timeout'  => 5.0,
		]);

		/**
		 * Request HTML
		 */
		$response = $client->request('GET', $subUrl);
		$body     = $response->getBody();
		$html     = $body->getContents();

		return $html;
	}

	/**
	 * @param $baseUrl
	 *
	 * @return bool
	 */
	public function scrap($baseUrl)
	{
		$this->baseUrl = $baseUrl;
		$html          = $this->getHtmlFromUrl($baseUrl);

		/**
		 *
		 */
		$articles = $this->extractHtml($html, '.record');

		foreach ($articles as $article)
		{
			/**
			 * Extract Author
			 * Only take articles with an author
			 */
			$author = $this->extractHtml($article, '.author > a');
			if (isset($author[0]))
			{
				$author = $this->cleanAuthor($author[0]);
				if ($author == '')
				{
					continue;
				}


				/**
				 * Extract Title
				 */
				$title = $this->extractHtml($article, '.headline > a');
				if (isset($title[0]))
				{
					$title = $this->cleanTitle($title[0]);
				}
				else
				{
					$title = '';
				}

				/**
				 * Extract Article URL
				 */
				$articleUrl = $this->extractHref($article, '.headline > a');

				/**
				 * Extract Author URL
				 */
				$authorUrl = $this->extractHref($article, '.author > a');

				$this->addItem($author, $authorUrl, $title, $articleUrl);
//				break;
			}
		}

		return true;
	}

	/**
	 * @param $str
	 *
	 * @return string
	 */
	public static function codify($str)
	{
		return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $str), '-'));
	}

	/**
	 * @param $haystack
	 * @param $needle
	 *
	 * @return bool
	 */
	public static function startsWith($haystack, $needle)
	{
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}

	/**
	 * @param $str
	 * @param $sub
	 *
	 * @return bool
	 */
	public static function endsWith($str, $sub)
	{
		return (substr($str, strlen($str) - strlen($sub)) === $sub);
	}
}


function pre($var = false)
{
	if ($var === true)
	{
		echo '<pre>';
		print_r("TRUE (boolean)");
		echo '</pre>';
		return;
	}
	if ($var === false)
	{
		echo '<pre>';
		print_r("FALSE (boolean)");
		echo '</pre>';
		return;
	}
	echo '<pre>';
	print_r($var);
	echo '</pre>';

	return;
}

/**
 * @param bool $var
 */
function prt($var = false)
{
	if ($var)
	{
		if (is_array($var))
		{
			print_r($var);
			return;
		}
		if ($var instanceof \stdClass)
		{
			print_r($var);
			return;
		}
		print $var . "\n";
		return;
	}
}

/**
 * @param $argv
 */
function placeholder($argv)
{
	$startDate           = false;
	$endDate             = false;
	$maxResultsPerAuthor = 0;

	foreach ($argv as $item)
	{
		if (Scraper::startsWith($item, '--startDate'))
		{
			$startDate = explode('=', $item)[1];
		}

		if (Scraper::startsWith($item, '--endDate'))
		{
			$endDate = explode('=', $item)[1];
		}

		if (Scraper::startsWith($item, '--maxResultsPerAuthor'))
		{
			$maxResultsPerAuthor = (int)explode('=', $item)[1];
		}
	}

//	prt($startDate);
	$startTime = strtotime($startDate);
//	prt(strtotime($startDate));

//	prt($endDate);
	$endTime = strtotime($endDate);
//	prt(strtotime($endDate));

	$scraper = new Scraper();
	$scraper->scrap('http://archive-grbj-2.s3-website-us-west-1.amazonaws.com/');
	$data = $scraper->getData();

	foreach ($data as $keyAuth => &$author)
	{
		foreach ($author['articles'] as $keyArt => $article)
		{
			$articleTime = strtotime($article['articleDate']);
//			prt($article['articleDate']);
//			prt(strtotime($article['articleDate']));

			/**
			 * Unset Article
			 */
			if ($articleTime < $startTime || $articleTime > $endTime)
			{
				unset($author['articles'][$keyArt]);
			}
		}

		/**
		 * Max results, if greater than zero
		 */
		if ($maxResultsPerAuthor > 0)
		{
			if (count($author['articles']) > $maxResultsPerAuthor)
			{
				$author['articles'] = array_slice($author['articles'], 0, $maxResultsPerAuthor);
			}
		}

		/**
		 * Unset Author
		 */
		if (count($author['articles']) <= 0)
		{
			unset($data[$keyAuth]);
		}
	}

	return $data;
}