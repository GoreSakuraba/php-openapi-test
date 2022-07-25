<?php

namespace Test\Rest\Classes;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class Handler
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $args
     *
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function getPetById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $pet = new Pet(
            $args['id'],
            new Category(101, "cat"),
            'Doris',
            [],
            [new Tag(1, 'gray')],
            'sold'
        );

        return $response->withBody(Utils::streamFor(json_encode($pet, JSON_THROW_ON_ERROR)));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $args
     *
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function addPet(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestBody = json_decode($request->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        $category = '';
        if (isset($requestBody->{'category'})) {
            $category = new Category(
                $requestBody->{'category'}->{'id'},
                $requestBody->{'category'}->{'name'}
            );
        }

        $tags = [];
        foreach ($requestBody->{'tags'} as $tag) {
            $tags[] = new Tag($tag->{'id'}, $tag->{'name'});
        }

        $pet = new Pet(
            $requestBody->{'id'} ?? '',
            $category,
            $requestBody->{'name'} ?? '',
            $requestBody->{'photoUrls'} ?? [],
            $tags,
            $requestBody->{'status'} ?? ''
        );

        if ($pet->getId() == '999') {
            // Simulate an error
            return $response->withBody(Utils::streamFor(json_encode(["status" => "ERROR"], JSON_THROW_ON_ERROR)));
        }

        // Expected empty response.
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $args
     *
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function processUpload(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $photoUrls = [];
        /** @var UploadedFileInterface $uploadedFile */
        foreach ($request->getUploadedFiles() as $uploadedFile) {
            $photoUrls['test'] = $uploadedFile->getClientFilename();
        }

        $tags = [];
        if (isset($request->getParsedBody()["note"])) {
            $tags[] = new Tag(1, $request->getParsedBody()["note"]);
        }

        $pet = new Pet(
            200,
            new Category(101, "cat"),
            'Doris',
            $photoUrls,
            $tags,
            'sold'
        );

        return $response->withBody(Utils::streamFor(json_encode($pet, JSON_THROW_ON_ERROR)));
    }
}
