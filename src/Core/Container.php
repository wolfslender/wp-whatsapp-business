<?php
/**
 * Container de inyección de dependencias
 *
 * @package WPWhatsAppBusiness\Core
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Core;

/**
 * Container simple de inyección de dependencias
 */
class Container {

    /**
     * Instancias registradas
     *
     * @var array
     */
    private $instances = [];

    /**
     * Bindings registrados
     *
     * @var array
     */
    private $bindings = [];

    /**
     * Singletons registrados
     *
     * @var array
     */
    private $singletons = [];

    /**
     * Registrar un binding
     *
     * @param string $abstract Clase abstracta o interfaz
     * @param callable $concrete Función de resolución
     * @return void
     */
    public function bind(string $abstract, callable $concrete): void {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Registrar un singleton
     *
     * @param string $abstract Clase abstracta o interfaz
     * @param callable $concrete Función de resolución
     * @return void
     */
    public function singleton(string $abstract, callable $concrete): void {
        $this->singletons[$abstract] = $concrete;
    }

    /**
     * Registrar una instancia
     *
     * @param string $abstract Clase abstracta o interfaz
     * @param mixed $instance Instancia
     * @return void
     */
    public function instance(string $abstract, $instance): void {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolver una dependencia
     *
     * @param string $abstract Clase abstracta o interfaz
     * @return mixed
     * @throws \Exception Si no se puede resolver la dependencia
     */
    public function get(string $abstract) {
        // Verificar si ya existe una instancia
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Verificar si es un singleton
        if (isset($this->singletons[$abstract])) {
            $instance = $this->singletons[$abstract]($this);
            $this->instances[$abstract] = $instance;
            return $instance;
        }

        // Verificar si es un binding
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]($this);
        }

        // Intentar crear una instancia automáticamente
        if (class_exists($abstract)) {
            return $this->resolveClass($abstract);
        }

        throw new \Exception("No se puede resolver la dependencia: {$abstract}");
    }

    /**
     * Verificar si existe un binding
     *
     * @param string $abstract Clase abstracta o interfaz
     * @return bool
     */
    public function has(string $abstract): bool {
        return isset($this->instances[$abstract]) ||
               isset($this->singletons[$abstract]) ||
               isset($this->bindings[$abstract]) ||
               class_exists($abstract);
    }

    /**
     * Resolver una clase automáticamente
     *
     * @param string $class Nombre de la clase
     * @return object
     * @throws \Exception Si no se puede crear la instancia
     */
    private function resolveClass(string $class) {
        $reflection = new \ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new \Exception("La clase {$class} no es instanciable");
        }

        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $class();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters());

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Resolver dependencias de un método
     *
     * @param \ReflectionParameter[] $parameters Parámetros del método
     * @return array
     */
    private function resolveDependencies(array $parameters): array {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if (is_null($dependency)) {
                // Parámetro primitivo
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("No se puede resolver el parámetro: {$parameter->getName()}");
                }
            } else {
                // Parámetro de tipo clase
                $dependencies[] = $this->get($dependency->getName());
            }
        }

        return $dependencies;
    }

    /**
     * Ejecutar un callback con dependencias inyectadas
     *
     * @param callable $callback Función a ejecutar
     * @param array $parameters Parámetros adicionales
     * @return mixed
     */
    public function call(callable $callback, array $parameters = []) {
        if (is_array($callback)) {
            $reflection = new \ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflection = new \ReflectionFunction($callback);
        }

        $dependencies = $this->resolveDependencies($reflection->getParameters());

        return call_user_func_array($callback, array_merge($dependencies, $parameters));
    }

    /**
     * Limpiar todas las instancias
     *
     * @return void
     */
    public function flush(): void {
        $this->instances = [];
    }

    /**
     * Obtener todas las instancias registradas
     *
     * @return array
     */
    public function getInstances(): array {
        return $this->instances;
    }

    /**
     * Obtener todos los bindings registrados
     *
     * @return array
     */
    public function getBindings(): array {
        return $this->bindings;
    }

    /**
     * Obtener todos los singletons registrados
     *
     * @return array
     */
    public function getSingletons(): array {
        return $this->singletons;
    }
} 