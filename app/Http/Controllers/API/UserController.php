<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

/**
 * Clase controladora de los Usuarios.
 * [Description UserController] en esta clase se procesa el CRUD de los usuarios.
 */

class UserController extends Controller
{
    /**
     * Guardar datos en JSON.
     * @return response json con los usuarios cargadas.
     */

    public function index(){

        $users = User::all();
        
        return response()->json($users);
    }

    /**
     * Funcion para validar la creacion correcta los usuarios.
     * 
     * Valida que la creacion de unos usuarios sea correcta.
     * 
     * @param Request $request solicitud pagina.
     * @return response Devuelve si el usuario fue creado correctamente en este caso (200 ok) en caso de error (dato error).
     */

    public function store(Request $request){

        $validator = Validator::make($request->all(),[
            'nombre' => 'required | string | max:50',
            'apellidos' => 'required | string | max:50',
            'telefono' => 'required | numeric | max:9',
            'ciudad' => 'required | string | max:70',
            'provincia' => 'required | string | max:70',
            'email' => 'required | email | max:50',
            'username' => 'required | string | max:50',
            'password'=> 'required | string | min:8',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $user = User::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
            'ciudad' => $request->ciudad,
            'provincia' => $request->provincia,
            'email' => $request->email,
            'username' => $request->username,
            'password' => password_hash($request->password, PASSWORD_DEFAULT),
        ]);

        return response()->json(["Usuario creado correctamente" , $user]);
    }
    
    /**
     * 
     * Funcion para mostrar los usuarios.
     * 
     * Esta funcion muestra los usuarios que existen.
     * 
     * @param mixed $id usuarios que hay.
     * @return response las usuarios que existen en la pagina.
     */

    public function show($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->statistics;
            $user->games;
        }

        if (is_null($user)) {
            return response()->json([
                'status'=> '404',
                'message'=> 'Datos no encontrados']); 
        }
        return response()->json([
            'status' => 200,
            'data' => new UserResource($user)]);
    }

    /**
     * Funcion para actualizar Usuarios.
     * 
     * Esta funcion actualiza las Usuarios.
     * 
     * @param Request $request solicitud
     * @param Post $post identificador del usuario.
     * @return response Devuelve en caso de todo correcto del usuario actualizado (200 ok) de lo contrario error.
     */

    public function update(Request $request, User $user){

        $validator = Validator::make($request->all(), [
            'nombre' => 'required | max:255',
            'apellidos' => 'required | max:50',
            'telefono' => 'required | max:9',
            'ciudad' => 'required | max:70',
            'provincia' => 'required | max:70',
            'email' => 'required | email | max:255 | unique:users,email,'.$user->id,
            'username' => 'required | max:50 | unique:users,username,'.$user->id,
            'password' => 'required | min:8'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errores' => $validator->messages(),
            ]);
        }

        $user->nombre = $request->nombre;
        $user->apellidos = $request->apellidos;
        $user->telefono = $request->telefono;
        $user->ciudad = $request->ciudad;
        $user->provincia = $request->provincia;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            "status" => 200,
            "message" => "Usuario actualizado correctamente" ,
            "data" => $user]);
    }

    /**
     * Funcion para borrar Usuarios.
     * 
     * Esta funcion elimina Usuarios.
     * 
     * @param Post $post identificador del usuario.
     * @return response Eliminacion del usuario correctamente (200 ok).
     */

    public function destroy(User $user){
        $user->delete();

        return response()->json(["Usuario eliminado Correctamente."]);
    }
    public function addAdmin(Request $request, User $user)
    {
        $user->admin = $request->admin;
        $user->save();

        return response()->json([
            "status" => 200,
            "message" => "Usuario actualizado correctamente" ,
            "data" => $user]);
    }

    /**
     * Funcion para actualizar la foto del perfil usuario.
     * 
     * Esta funcion actualiza el perfil del usuario cuando desee cambiar su foto (trae una por defecto en caso de nunca haberla cambiado).
     * @param Request $request
     * @param User $user
     * 
     * @return [response] Retorna la imagen subida al servidor.
     */

    public function updateProfile(Request $request,User $user)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required | image | max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errores' => $validator->messages(),
            ]);
        }

        $filename = $user->username. '.' .$request->avatar->getClientOriginalExtension();
        $request->avatar->move(public_path('uploads/avatars'),$filename);

        $user->foto = $filename;
        $user->save();
        return response()->json($filename);
    }

    /**
     * Funcion obtener foto perfil.
     * 
     * Funcion para obtener la foto que tiene ese usuario en su perfil
     * @param User $user
     * 
     * @return [response] devuelve la foto del perfil del usuario
     */
    
    public function getProfile(User $user)
    {
        return response()->json($user->foto);
    }
}
