<?php

namespace App\Http\Controllers\Muni;

use App\Models\FuncionarioMunicipal;
use App\Http\Controllers\Controller;  
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $data['es_admin'] = (bool) ($request->boolean('es_admin'));

        FuncionarioMunicipal::create($data);

        return redirect()->route('funcionarios.index')->with('success','Funcionario creado correctamente.');
    }

    public function toggleAdmin($id)
    {
        $yo = auth('func')->user();
        if ((int)$yo->id === (int)$id) {
            return back()->with('error','No puedes cambiar tu propio rol.');
        }

        $f = \App\Models\FuncionarioMunicipal::findOrFail($id);
        $f->es_admin = !$f->es_admin;
        $f->save();

        return back()->with('success','Rol actualizado.');
    }

    public function destroy($id)
    {
        $yo = auth('func')->user();

        if ((int)$yo->id === (int)$id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $funcionario = \App\Models\FuncionarioMunicipal::findOrFail($id);
        $funcionario->delete();

        return back()->with('success', 'Funcionario eliminado correctamente.');
    }

}