<?php
declare(strict_types=1);
/**
 * @copyright see PROJECT_LICENSE.txt
 *
 * @see       PROJECT_LICENSE.txt
 */

namespace Eurotext\RestApiClient\Test\Integration\Api;

use Eurotext\RestApiClient\Api\Project\ItemV1Api;
use Eurotext\RestApiClient\Api\ProjectV1Api;
use Eurotext\RestApiClient\Configuration;
use Eurotext\RestApiClient\Enum\ProjectTypeEnum;
use Eurotext\RestApiClient\Request\Data\Project\ItemData;
use Eurotext\RestApiClient\Request\Data\ProjectData;
use Eurotext\RestApiClient\Request\Project\ItemDataRequest;
use Eurotext\RestApiClient\Request\ProjectDataRequest;
use PHPUnit\Framework\TestCase;

class FullV1ApiIntegrationTest extends TestCase
{
    /** @var ProjectV1Api */
    private $projectV1Api;

    /** @var ItemV1Api */
    private $projectItemV1Api;

    const PROJECT_DESCRIPTION = 'Integration Test';

    protected function setUp()
    {
        parent::setUp();

        $config = new Configuration();
        $config->setApiKey(\constant('EUROTEXT_API_KEY'));

        $this->projectV1Api     = new ProjectV1Api($config);
        $this->projectItemV1Api = new ItemV1Api($config);
    }

    /**
     * @throws \Eurotext\RestApiClient\Exception\DeserializationFailedException
     */
    public function testItShouldCreateProject()
    {
        $projectData = new ProjectData(self::PROJECT_DESCRIPTION);

        $request = new ProjectDataRequest('', $projectData, ProjectTypeEnum::QUOTE());

        $response = $this->projectV1Api->post($request);

        $this->assertGreaterThan(0, $response->getId());

        return $response->getId();
    }

    /**
     * @depends testItShouldCreateProject
     */
    public function testItShouldCreateItem(int $projectId)
    {
        $itemRequest = new ItemDataRequest(
            $projectId,
            'en-us',
            'de-de',
            'product',
            'Magento',
            new ItemData(
                [
                    'description' => 'Please translate me!',
                ],
                [
                    'item_id'   => 27,
                    'more_meta' => 'eurotext are the best',
                ]
            )
        );

        $response = $this->projectItemV1Api->post($itemRequest);

        $this->assertGreaterThan(0, $response->getId());
    }

    /**
     * @depends testItShouldCreateProject
     */
    public function testItShouldGetProjectData(int $projectId)
    {
        $response = $this->projectV1Api->get($projectId);

        $actualItem   = $response->getItems()[1];
        $expectedItem = [
            'original_string' => 'Please translate me!',
            '__meta'          => [
                'item_id'   => 27,
                'more_meta' => 'eurotext are the best',
            ],
            'status'          => '',
        ];

        $this->assertSame($response->getDescription(), self::PROJECT_DESCRIPTION);
        $this->assertSame(
            $expectedItem, $actualItem,
            "Item for project-id $projectId does not match"
        );
    }

    /**
     * @depends testItShouldCreateProject
     */
    public function testItShouldGetItemData(int $projectId)
    {
        $this->assertTrue(true);
        $this->markTestIncomplete('Implement, when get is implemented');
    }
}