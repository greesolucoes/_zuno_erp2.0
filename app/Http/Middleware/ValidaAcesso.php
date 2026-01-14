<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Venda;
use App\Models\Usuario;
use App\Models\ConfigNota;
use App\Helpers\Menu;

class ValidaAcesso
{
	public function handle($request, Closure $next){

		$value = session('user_logged');

		if(!$value){
			$protocolo = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
			$uri = $_SERVER['REQUEST_URI'];
			$host = $_SERVER['HTTP_HOST'];

			$uri = $protocolo . $host . $uri;
			return redirect("/login")->with('uri', $uri);
		}

		if($request->ajax()){
			return $next($request);
		}

		if($value['super']){
			return $next($request);
		}

		$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
		$uri = $this->permissionKeyFromPath($requestPath);
		$value = session('user_logged');
		$usuario = Usuario::find($value['id']);
		$permissao = json_decode($usuario->permissao);	
		// dd($permissao);
		// die();
		foreach($permissao as $p){
			if($this->permissionKeyFromPath($this->normalizeMenuRoute($p)) == $uri){
				return $next($request);
			}
		}
		$valida = $this->validaRotaInexistente($uri);
		
		if($valida == true){
			return redirect('/error');
		}else{
			// se a rota nao disponivel no helper menu.php quer dizer que não precisa ser controlada
			return $next($request);
		}
	}

	private function validaRotaInexistente($uri){
		$existe = false;

		$menu = new Menu();
		$menu = $menu->getMenu();
		foreach($menu as $m){
			foreach($m['subs'] as $s){

				if($this->permissionKeyFromPath($this->normalizeMenuRoute($s['rota'] ?? null)) == $uri){
					$existe = true;
				}
			}
		}
		return $existe;
	}

	private function permissionKeyFromPath($path)
	{
		if(!is_string($path) || $path == ''){
			return '';
		}

		$parts = array_values(array_filter(explode('/', trim($path, '/'))));
		if(sizeof($parts) == 0){
			return '/';
		}

		$base = array_slice($parts, 0, 2);
		return '/' . implode('/', $base);
	}

	private function normalizeMenuRoute($value)
	{
		if(!is_string($value) || $value == ''){
			return '';
		}

		$path = parse_url($value, PHP_URL_PATH);
		return is_string($path) && $path != '' ? $path : $value;
	}

}
