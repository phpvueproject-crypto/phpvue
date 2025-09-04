<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Auth;
use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class ProjectController extends Controller {
    public function index(Request $request): array {
        $projects = new Project();
        $isDeploy = $request->input('is_deploy');
        if($isDeploy) {
            $projects = $projects->where('is_deploy', $isDeploy);
        }

        $name = $request->input('name');
        if($name) {
            $projects = $projects->where('name', 'like', "%$name%");
        }

        $isRead = $request->input('is_read');
        $isWrite = $request->input('is_write');
        $projects = $projects->with([
            'regionMgmts' => function(HasMany $query) use ($isRead, $isWrite) {
                $query->orderBy('region');
                $user = Auth::user();
                if($user) {
                    $query->whereRelation('users', 'id', $user->id);
                    $query->whereHas('users', function(Builder $query) use ($user, $isRead, $isWrite) {
                        $query->where('user_id', $user->id);
                        if($isRead !== null) {
                            $query->where('is_read', $isRead);
                        }
                        if($isWrite !== null) {
                            $query->where('is_write', $isWrite);
                        }
                    });
                }
            },
            'regionMgmts.editUser'
        ])->withCount('regionMgmts')->get();

        return [
            'status' => 0,
            'data'   => [
                'projects' => $projects
            ]
        ];
    }

    public function show($id): array {
        /** @var Project $project */
        $project = Project::with([
            'regionMgmts.roomEnvironment',
            'regionMgmts.microOrganism'
        ])->findOrFail($id);

        return [
            'status' => 0,
            'data'   => [
                'project' => $project
            ]
        ];
    }

    public function displayCad($projectName): Response|Application|ResponseFactory {
        $path = storage_path("app/projects/$projectName/cad_{$projectName}.png");
        try {
            $file = File::get($path);
        } catch(FileNotFoundException) {
            return response(null, 404);
        }

        return response($file, 200, [
            'Content-Type' => File::mimeType($path)
        ]);
    }
}
