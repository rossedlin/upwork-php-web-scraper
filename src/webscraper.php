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
	private $data = [];

	/**
	 * @param $author
	 * @param $title
	 */
	private function addItem($author, $title)
	{
		$code = self::codify($author);

		pre('<span style="color: #00ff00;">' . $code . '</span>');
		pre('<span style="color: #ff0000;">' . $author . '</span>');
		pre($title);

		$article = [
			'articleTitle' => $title,
			'articleUrl'   => 'http://example.com',
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
			$this->data[$code] = [
				'authorName'  => $author,
				'authorCount' => 1,
				'articles'    => [$article],
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
	private function filterHtml($html, $filter)
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

	/**
	 * @return mixed
	 */
	public function scrap()
	{
		$client = new Client([
			'base_uri' => 'http://archive-grbj-2.s3-website-us-west-1.amazonaws.com/',
			'timeout'  => 5.0,
		]);

		/**
		 * Request HTML
		 */
		$response = $client->request('GET', '/');
		$body     = $response->getBody();
		$html     = $body->getContents();

		/**
		 *
		 */
		$articles = $this->filterHtml($html, '.record');

		foreach ($articles as $article)
		{
			/**
			 * Extract Author
			 * Only take articles with an author
			 */
			$author = $this->filterHtml($article, '.author > a');
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
				$title = $this->filterHtml($article, '.headline > a');
				$title = $this->cleanTitle($title[0]);

				$this->addItem($author, $title);
			}


//			$titles[] = $this->cleanTitle($title);

//			break;
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

	public static function codify($str)
	{
		return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $str), '-'));
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


$scraper = new Scraper();
pre($scraper->scrap());
pre($scraper->getData());