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

		pre('<span style="color: #00ff00;">' . $code . '</span>');
		pre('<span style="color: #ff0000;">' . $author . '</span>');
		pre($authorUrl);
		pre($title);
		pre($articleUrl);

		$article = [
			'articleTitle' => $title,
			'articleUrl'   => $articleUrl,
			'articleDate'  => '2001-01-01',
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
		$crawler  = new Crawler($html);
		$articles = $crawler
			->filter($filter)
			->each(function (Crawler $node)
			{
				return $node->html();
			});

		return $articles;
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
				$title = $this->cleanTitle($title[0]);

				/**
				 * Extract Article URL
				 */
				$articleUrl = $this->extractHref($article, '.headline > a');
				if (strlen(trim($articleUrl)) > 1)
				{
					if (!self::startsWith($articleUrl, $baseUrl))
					{
						$articleUrl = $baseUrl . $articleUrl;
					}
				}

				/**
				 * Extract Author URL
				 */
				$authorUrl = $this->extractHref($article, '.author > a');
//				if (strlen(trim($authorUrl)) > 1)
//				{
//					if (!self::startsWith($authorUrl, $baseUrl))
//					{
//						$authorUrl = $baseUrl . $authorUrl;
//					}
//				}

				$this->addItem($author, $authorUrl, $title, $articleUrl);
				break;
			}
		}

//		pre($titles);

//		$cats          = $subCatsHTML = array();
//		$catsFilter    = '.all-depts-links-heading > a';
//		$subCatsFilter = 'ul';

		/**
		 *
		 */
//		foreach ($articles as $index => $catHTML)
//		{
//			$crawler             = new Crawler($catHTML);
//			$cats[]              = $crawler
//				->filter($catsFilter)
//				->text();
//			$subCatsHTML[$index] = array();
//			$subCatsHTML[$index] = $crawler
//				->filter($subCatsFilter)
//				->each(function (Crawler $node)
//				{
//					return $node->html();
//				});
//			unset($crawler);
//
//		}

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
//    public static function endsWith($haystack, $needle) {
//        // search forward starting from end minus needle length characters
//        return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
//    }
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


$scraper = new Scraper();
pre($scraper->scrap('http://archive-grbj-2.s3-website-us-west-1.amazonaws.com/'));
pre($scraper->getData());