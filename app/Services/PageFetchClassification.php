<?php

namespace App\Services;

enum PageFetchClassification: string
{
    case Ok = 'ok';
    case TransportError = 'transport_error';
    case HttpError = 'http_error';
    case NonHtml = 'non_html';
    case ErrorPage = 'error_page';
}
