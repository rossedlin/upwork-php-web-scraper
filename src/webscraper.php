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

	private function addItem($author, $title)
	{
//		if ($this->[$author])
//
//		$item =
//			[
//				'authorName' => $author,
//				'articles'   => [
//					[
//						'articleUrl'  => 'http://example.com',
//						'articleDate' => '2001-01-01',
//					],
//					[],
//					[],
//				],
//			];
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
		return $str;
		return trim($str);
	}

	/**
	 * @return mixed
	 */
	public function test1()
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

		$titles = [];
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

				pre('<span style="color: #ff0000;">' . $author . '</span>');

				/**
				 * Extract Title
				 */
				$title = $this->filterHtml($article, '.headline > a');
				pre($title);

				$this->addItem($author, $title);
			}


//			$titles[] = $this->cleanTitle($title);

//			break;
		}

//		pre($titles);

//		$cats          = $subCatsHTML = array();
//		$catsFilter    = '.all-depts-links-heading > a';
//		$subCatsFilter = 'ul';

		exit;

		/**
		 *
		 */
		foreach ($articles as $index => $catHTML)
		{
			$crawler             = new Crawler($catHTML);
			$cats[]              = $crawler
				->filter($catsFilter)
				->text();
			$subCatsHTML[$index] = array();
			$subCatsHTML[$index] = $crawler
				->filter($subCatsFilter)
				->each(function (Crawler $node)
				{
					return $node->html();
				});
			unset($crawler);

		}

		return $body->getContents();
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
pre($scraper->test1());