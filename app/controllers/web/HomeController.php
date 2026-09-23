<?php
namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Models\CategoryModel;

/**
 * HomeController — Trang chủ
 * GET / → index()
 */
class HomeController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(): void
    {
        $catModel   = new CategoryModel();
        $categories = $catModel->findAll([], 'id ASC');

        // Flash messages
        $flashError   = $this->getFlash('error');
        $flashSuccess = $this->getFlash('success');

        $this->render('home.index', compact('categories', 'flashError', 'flashSuccess'));
    }
}
