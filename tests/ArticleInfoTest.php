<?php
namespace Bold\PIMService\Test;


class ArticleInfoTest extends Client
{
    public function testArticleSync()
    {
        $result = new \stdClass;
        $result->result = null;
        $result->v_info = file_get_contents(__DIR__ . '/_files/articleinfoResponse1.xml');

        $this->soapMock->expects($this->any())
            ->method('articleinfo')
            ->willReturn($result);

        $results = $this->client->pullArticleInfo([1234567, 9876]);


        self::assertEquals(1, count($results), "Expected 1 response object");

        self::assertEquals(2, count($results[0]->getArticles()), "Expected 2 articles in response object");

        $article1 = $results[0]->getArticles()[0];

        $article2 = $results[0]->getArticles()[1];

        self::assertEquals("true", $article1->getDeliverable(), "Expected the first article to be deliverable");

        self::assertEquals(3, count($article1->getPriceTiers()), "Expected 3 tier prices in the first article object");
        self::assertEquals(12, $article1->getPriceTiers()[2]['amount'], "Qty of third tier of first article is incorrect");
        self::assertEquals("6,4990", $article1->getPriceTiers()[2]['price'], "Price of the third tier of the first article is incorrect");
        self::assertEquals(1, $article1->getPriceTiers()[0]['amount'], "Qty of first tier of first article is incorrect");
        self::assertEquals("7,990", $article1->getPriceTiers()[0]['price'], "Price of the first tier of the first article is incorrect");
        self::assertEquals("no article found", $article2->getStatus(), "Expected the second article to have status no article found");


    }
}
