<?php
/**
 * Shenava - Books Controller
 * Handles book-related endpoints
 */

class BooksController extends ApiController
{

    private BookModel $bookModel;
    private CategoryModel $categoryModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->bookModel = new BookModel();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Get all books with pagination and filters
     */
    public function index()
    {
        try {
            $pagination = $this->getPaginationParams();
            $filters = [
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset'],
                'category' => $_GET['category'] ?? null,
                'search' => $_GET['search'] ?? null,
                'featured' => isset($_GET['featured']) ? true : null
            ];

            $books = $this->bookModel->getBooks($filters);

            // Get total count for pagination
            $total = $this->bookModel->count();

            $response = [
                'books' => $books,
                'pagination' => [
                    'page' => $pagination['page'],
                    'limit' => $pagination['limit'],
                    'total' => $total,
                    'pages' => ceil($total / $pagination['limit'])
                ]
            ];

            return $this->success($response);

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get a single book by UUID
     */
    public function show($params)
    {
        try {
            $uuid = $params['uuid'] ?? '';

            if (empty($uuid)) {
                return $this->error('Book UUID is required', 422);
            }

            $book = $this->bookModel->getBookByUuid($uuid);

            if (!$book) {
                return $this->error('Book not found', 404);
            }

            // Increment views
            $this->bookModel->incrementViews($book->id);

            // Get chapters
            $chapters = $this->bookModel->getChapters($book->id);

            $response = [
                'book' => $book,
                'chapters' => $chapters
            ];

            return $this->success($response);

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get featured books
     */
    public function featured()
    {
        try {
            $limit = min(20, max(1, intval($_GET['limit'] ?? 10)));
            $books = $this->bookModel->getFeaturedBooks($limit);

            return $this->success(['books' => $books]);

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get books by category
     */
    public function byCategory($params)
    {
        try {
            $categorySlug = $params['slug'] ?? '';
            $pagination = $this->getPaginationParams();

            if (empty($categorySlug)) {
                return $this->error('Category slug is required', 422);
            }

            // Verify category exists
            $category = $this->categoryModel->getBySlug($categorySlug);

            if (!$category) {
                return $this->error('Category not found', 404);
            }

            $options = [
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset']
            ];

            $books = $this->bookModel->getBooksByCategory($categorySlug, $options);
            $total = $this->bookModel->count(); // This needs a method for category count

            $response = [
                'category' => $category,
                'books' => $books,
                'pagination' => [
                    'page' => $pagination['page'],
                    'limit' => $pagination['limit'],
                    'total' => $total,
                    'pages' => ceil($total / $pagination['limit'])
                ]
            ];

            return $this->success($response);

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}