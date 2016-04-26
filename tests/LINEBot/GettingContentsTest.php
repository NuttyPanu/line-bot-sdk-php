<?php
/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
namespace LINE\Tests\LINEBot;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LINE\LINEBot;

class GettingContentsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUserProfile()
    {
        $mock = new MockHandler([
            function (Request $req) {
                $this->assertEquals($req->getMethod(), 'GET');
                $this->assertEquals(
                    $req->getUri(),
                    'https://trialbot-api.line.me/v1/profiles?mids=DUMMY_MID_GET_DISPLAY_NAME'
                );


                $channelIdHeader = $req->getHeader('X-Line-ChannelID');
                $this->assertEquals(sizeof($channelIdHeader), 1);
                $this->assertEquals($channelIdHeader[0], '1000000000');

                $channelSecretHeader = $req->getHeader('X-Line-ChannelSecret');
                $this->assertEquals(sizeof($channelSecretHeader), 1);
                $this->assertEquals($channelSecretHeader[0], 'testsecret');

                $channelMidHeader = $req->getHeader('X-Line-Trusted-User-With-ACL');
                $this->assertEquals(sizeof($channelMidHeader), 1);
                $this->assertEquals($channelMidHeader[0], 'TEST_MID');

                return new Response(
                    200,
                    [],
                    '{"start":1,"display":1,"total":1,"count":1,"contacts":[{"displayName":"BOT API","mid":"u0047556f2e40dba2456887320ba7c76d","pictureUrl":"http://example.com/abcdefghijklmn","statusMessage":"Hello, LINE!"}]}'
                );
            },
        ]);
        $mockHandler = HandlerStack::create($mock);

        $config = [
            'channelId' => '1000000000',
            'channelSecret' => 'testsecret',
            'channelMid' => 'TEST_MID',
        ];
        $sdk = new LINEBot(
            $config,
            new LINEBot\HTTPClient\GuzzleHTTPClient(array_merge($config, ['handler' => $mockHandler]))
        );

        $res = $sdk->getUserProfile('DUMMY_MID_GET_DISPLAY_NAME');
        $this->assertEquals($res['count'], 1);
        $this->assertEquals($res['contacts'][0]['displayName'], 'BOT API');
    }
}
