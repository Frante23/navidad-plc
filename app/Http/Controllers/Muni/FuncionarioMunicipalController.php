<?php

namespace App\Http\Controllers\Muni;

use App\Models\FuncionarioMunicipal;
use App\Http\Controllers\Controller;  
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;

class FuncionarioMunicipalController extends Controller
{
    public function index(Request $request)
    {
        $q     = trim($request->input('q', ''));
        $rol   = $request->input('rol', 'todos');
        $sort  = $request->input('sort', 'nombre_completo'); 
        $dir   = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortMap = [
            'nombre_completo' => 'nombre_completo',
            'rut'             => 'rut',
        ];
        $sortCol = $sortMap[$sort] ?? 'nombre_completo';

        $query = \App\Models\FuncionarioMunicipal::query();

        if ($rol === 'admins')        $query->where('es_admin', 1);
        elseif ($rol === 'usuarios')  $query->where('es_admin', 0);

        if ($q !== '') {
            $qRut = preg_replace('/[^0-9K]/i', '', $q);

            $query->where(function($w) use ($q, $qRut) {
                $w->where('nombre_completo', 'like', "%{$q}%");

                if ($qRut !== '') {
                    $w->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(UPPER(rut),'.',''),'-',''),' ','') LIKE ?",
                        ["%".strtoupper($qRut)."%"]
                    );
                }
            });
        }

        $query->orderBy($sortCol, $dir);

        $funcionarios = $query->paginate(15)->appends($request->query());

        return view('municipales.view-func', compact('funcionarios','q','rol','sort','dir'));
    }

    public function create()
    {
        return view('municipales.create-func');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo'   => ['required','string','max:150'],
            'rut'               => ['required','string','max:15','regex:/^[0-9kK\.\-]+$/', Rule::unique('funcionarios_municipales','rut')],
            'correo'            => ['required','email','max:150', Rule::unique('funcionarios_municipales','correo')],
            'telefono_contacto' => ['nullable','string','max:30'],
            'cargo'             => ['nullable','string','max:100'],
            'password'          => ['required','string','min:8'],
            'es_admin'          => ['nullable','boolean'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['es_admin'] = $request->boolean('es_admin');

        $nuevo = FuncionarioMunicipal::create($data);

        DB::statement(
            'CALL sp_audit_log(?,?,?,?,?,?,?,?)',
            [
                auth('func')->id(),
                'FUNC_CREATE',
                'funcionario',
                $nuevo->id,
                'Creó funcionario '.$nuevo->nombre_completo.' ('.$nuevo->correo.')',
                request()->ip(),
                substr((string)request()->header('User-Agent'),0,255),
                json_encode(['payload'=>$request->except(['password','_token'])], JSON_UNESCAPED_UNICODE),
            ]
        );


        return redirect()->route('funcionarios.index')
            ->with('success','Funcionario creado correctamente.');
    }


    public function toggleAdmin($id)
    {
        $yo = auth('func')->user();
        if ((int)$yo->id === (int)$id) {
            return back()->with('error','No puedes cambiar tu propio rol.');
        }

        $f = FuncionarioMunicipal::findOrFail($id);
        $was = (bool)$f->es_admin;
        $f->es_admin = !$f->es_admin;
        $f->save();

        Audit::log(
            null,
            $was ? 'FUNC_REVOKE_ADMIN' : 'FUNC_GRANT_ADMIN',
            'funcionario',
            $f->id,
            ($was ? 'Quitó admin a ' : 'Concedió admin a ').$f->nombre_completo.' ('.$f->correo.')',
            ['before'=>['es_admin'=>$was],'after'=>['es_admin'=>$f->es_admin]]
        );

        return back()->with('success','Rol actualizado.');
    }

    public function destroy($id)
    {
        $yo = auth('func')->user();
        if ((int)$yo->id === (int)$id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $f = FuncionarioMunicipal::findOrFail($id);

        Audit::log(
            null,
            'FUNC_DELETE',
            'funcionario',
            $f->id,
            'Eliminó funcionario '.$f->nombre_completo.' ('.$f->correo.')',
            ['snapshot'=>$f->toArray()]
        );

        $f->delete();

        return back()->with('success','Funcionario eliminado correctamente.');
    }








}