<?php
require_once __DIR__ . '/../app/Controllers/BookController.php';

function dispatch($route, $db) {
    $bookController = new BookController($db);

    switch ($route) {
        // Quản lý sách
        case 'books':
        case '':
            $bookController->index();
            break;
        case 'books/create':
            $bookController->create();
            break;
        case 'books/store':
            $bookController->store();
            break;
        case 'books/edit':
            $bookController->edit();
            break;
        case 'books/update':
            $bookController->update();
            break;
        case 'books/delete':
            $bookController->delete();
            break;

        // Quản lý danh mục (thể loại)
        case 'categories':
            $bookController->categories();
            break;
        case 'categories/store':
            $bookController->storeCategory();
            break;
        case 'categories/update':
            $bookController->updateCategory();
            break;
        case 'categories/delete':
            $bookController->deleteCategory();
            break;

        default:
            http_response_code(404);
            echo "404 - Không tìm thấy trang yêu cầu.";
            break;
    }
}