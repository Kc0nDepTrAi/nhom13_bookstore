<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\CategoryModel;

/**
 * CategoryApiController — REST API CRUD thể loại
 *
 * GET    /api/categories        → index()
 * GET    /api/categories/{id}   → show()
 * POST   /api/categories        → store()  (admin)
 * PUT    /api/categories/{id}   → update() (admin)
 * DELETE /api/categories/{id}   → destroy()(admin)
 */
class CategoryApiController extends Controller
{
    private CategoryModel $model;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->model = new CategoryModel();
    }

    public function index(): void
    {
        $categories = $this->model->getAllWithBookCount();
        $this->success($categories);
    }

    public function show(string $id): void
    {
        $cat = $this->model->findById((int) $id);
        if (!$cat) {
            $this->notFound("Không tìm thấy thể loại với id = $id");
            return;
        }
        $this->success($cat);
    }

    public function store(): void
    {
        $this->requireAdmin();

        $name = trim((string) $this->request->input('name', ''));
        if (!$name) {
            $this->error('Tên thể loại không được để trống');
            return;
        }
        if ($this->model->nameExists($name)) {
            $this->error('Tên thể loại đã tồn tại', 409);
            return;
        }
        $id = $this->model->create(['name' => $name]);
        $this->success(['id' => $id], 'Thêm thể loại thành công', 201);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();

        $catId = (int) $id;
        if (!$this->model->exists($catId)) {
            $this->notFound("Không tìm thấy thể loại với id = $catId");
            return;
        }

        $name = trim((string) $this->request->input('name', ''));
        if (!$name) {
            $this->error('Tên thể loại không được để trống');
            return;
        }
        if ($this->model->nameExists($name, $catId)) {
            $this->error('Tên thể loại đã tồn tại', 409);
            return;
        }

        $this->model->update($catId, ['name' => $name]);
        $this->success(null, 'Cập nhật thể loại thành công');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();

        $catId = (int) $id;
        if (!$this->model->exists($catId)) {
            $this->notFound("Không tìm thấy thể loại với id = $catId");
            return;
        }
        $this->model->delete($catId);
        $this->success(null, 'Xóa thể loại thành công');
    }
}
