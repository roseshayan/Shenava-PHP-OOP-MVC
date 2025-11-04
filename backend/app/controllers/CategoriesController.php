<?php
/**
 * Shenava - Categories Controller
 * Handles category-related endpoints
 */

class CategoriesController extends ApiController
{

    private CategoryModel $categoryModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Get all categories
     */
    public function index()
    {
        try {
            $categories = $this->categoryModel->getCategories();
            return $this->success(['categories' => $categories]);

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}