<?php

namespace App\Http\Controllers;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Http\Request;
use GuzzleHttp\Client;


class AiController extends Controller
{
    public function createFakeComments(Request $request)
    {
        $client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . 'YOUR_API_KEY',
                'Content-Type' => 'application/json'
            ]
        ]);

        $productName = $request->input('productName');
        $commentType = $request->input('commentType');
        $commentCount = $request->input('commentCount');

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a random comment generating AI.'],
                    ['role' => 'user', 'content' => 'Generate ' . $commentCount . ' ' . $commentType . ' comments for the product: ' . $productName . '.'],
                    ['role' => 'system', 'content' => 'Please provide each comment as a JSON object: {"author": "author name", "comment": "generated comment"}. Separate multiple comments with a comma. If the product is not a real product, or the product is not in the e-commerce websites return "NO_COMMENT".']
                ],
            ],
        ]);

        $result = json_decode($response->getBody(), true);
        $response_text = $result['choices'][0]['message']['content'];

        // If the response is "NO_COMMENT", return it directly
        if (trim($response_text) === "NO_COMMENT") {
            return response()->json([
                'result' => "NO_COMMENT",
                'message' => 'success',
            ]);
        }

        // Clean up the response text to create a proper JSON string
        $response_text = '[' . trim($response_text) . ']';

        // Decode the generated JSON string into an array
        $comments = json_decode($response_text, true);

        // Check if the comments array is valid, otherwise return an error message
        if (!is_null($comments)) {
            return response()->json([
                'result' => $comments,
                'message' => 'success',
            ]);
        } else {
            return response()->json([
                'result' => null,
                'message' => 'Failed to parse comments.',
            ]);
        }
    }
}