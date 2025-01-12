<?php

namespace App\Controllers;

use App\GraphQL\Types;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Schema;
use RuntimeException;
use Throwable;

class GraphQL
{
    static public function handle()
    {
        try {
            $schema = new Schema([
                'query' => Types::query(),
                'mutation' => Types::mutation()
            ]);

            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);
            $query = $input['query'];
            $variableValues = $input['variables'] ?? null;

            $result = GraphQLBase::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray(true);
        } catch (Throwable $e) {
            error_log('GraphQL error details: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $output = [
                'errors' => [
                    [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'locations' => [
                            [
                                'line' => $e->getLine(),
                                'file' => $e->getFile()
                            ]
                        ]
                    ]
                ]
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }
}
