<?php
/**
 * Loader de hooks para WordPress
 *
 * @package WPWhatsAppBusiness\Core
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Core;

/**
 * Clase para gestionar hooks de WordPress
 */
class Loader {

    /**
     * Hooks registrados
     *
     * @var array
     */
    private $hooks = [];

    /**
     * Filtros registrados
     *
     * @var array
     */
    private $filters = [];

    /**
     * Acciones registradas
     *
     * @var array
     */
    private $actions = [];

    /**
     * Agregar una acción
     *
     * @param string $hook Hook de WordPress
     * @param object $component Componente que contiene el callback
     * @param string $callback Método del componente
     * @param int $priority Prioridad (por defecto 10)
     * @param int $accepted_args Número de argumentos aceptados (por defecto 1)
     * @return void
     */
    public function addAction(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1): void {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Agregar un filtro
     *
     * @param string $hook Hook de WordPress
     * @param object $component Componente que contiene el callback
     * @param string $callback Método del componente
     * @param int $priority Prioridad (por defecto 10)
     * @param int $accepted_args Número de argumentos aceptados (por defecto 1)
     * @return void
     */
    public function addFilter(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1): void {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Agregar un hook genérico
     *
     * @param array $hooks Array de hooks
     * @param string $hook Hook de WordPress
     * @param object $component Componente que contiene el callback
     * @param string $callback Método del componente
     * @param int $priority Prioridad
     * @param int $accepted_args Número de argumentos aceptados
     * @return array
     */
    private function add(array $hooks, string $hook, $component, string $callback, int $priority, int $accepted_args): array {
        $hooks[] = [
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        ];

        return $hooks;
    }

    /**
     * Ejecutar todos los hooks registrados
     *
     * @return void
     */
    public function run(): void {
        // Registrar acciones
        foreach ($this->actions as $hook) {
            add_action(
                $hook['hook'],
                [$hook['component'], $hook['callback']],
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        // Registrar filtros
        foreach ($this->filters as $hook) {
            add_filter(
                $hook['hook'],
                [$hook['component'], $hook['callback']],
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }

    /**
     * Agregar múltiples hooks de una vez
     *
     * @param array $hooks Array de hooks con estructura:
     * [
     *     'hook' => 'hook_name',
     *     'component' => $component,
     *     'callback' => 'method_name',
     *     'priority' => 10,
     *     'accepted_args' => 1,
     *     'type' => 'action' // o 'filter'
     * ]
     * @return void
     */
    public function addHooks(array $hooks): void {
        foreach ($hooks as $hook) {
            $type = $hook['type'] ?? 'action';
            
            if ($type === 'filter') {
                $this->addFilter(
                    $hook['hook'],
                    $hook['component'],
                    $hook['callback'],
                    $hook['priority'] ?? 10,
                    $hook['accepted_args'] ?? 1
                );
            } else {
                $this->addAction(
                    $hook['hook'],
                    $hook['component'],
                    $hook['callback'],
                    $hook['priority'] ?? 10,
                    $hook['accepted_args'] ?? 1
                );
            }
        }
    }

    /**
     * Remover una acción
     *
     * @param string $hook Hook de WordPress
     * @param object $component Componente que contiene el callback
     * @param string $callback Método del componente
     * @param int $priority Prioridad
     * @return void
     */
    public function removeAction(string $hook, $component, string $callback, int $priority = 10): void {
        remove_action($hook, [$component, $callback], $priority);
    }

    /**
     * Remover un filtro
     *
     * @param string $hook Hook de WordPress
     * @param object $component Componente que contiene el callback
     * @param string $callback Método del componente
     * @param int $priority Prioridad
     * @return void
     */
    public function removeFilter(string $hook, $component, string $callback, int $priority = 10): void {
        remove_filter($hook, [$component, $callback], $priority);
    }

    /**
     * Limpiar todos los hooks registrados
     *
     * @return void
     */
    public function clear(): void {
        $this->actions = [];
        $this->filters = [];
        $this->hooks = [];
    }

    /**
     * Obtener todas las acciones registradas
     *
     * @return array
     */
    public function getActions(): array {
        return $this->actions;
    }

    /**
     * Obtener todos los filtros registrados
     *
     * @return array
     */
    public function getFilters(): array {
        return $this->filters;
    }

    /**
     * Obtener todos los hooks registrados
     *
     * @return array
     */
    public function getAllHooks(): array {
        return [
            'actions' => $this->actions,
            'filters' => $this->filters
        ];
    }

    /**
     * Verificar si un hook está registrado
     *
     * @param string $hook Nombre del hook
     * @param string $type Tipo de hook ('action' o 'filter')
     * @return bool
     */
    public function hasHook(string $hook, string $type = 'action'): bool {
        $hooks = $type === 'filter' ? $this->filters : $this->actions;
        
        foreach ($hooks as $registered_hook) {
            if ($registered_hook['hook'] === $hook) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtener hooks por nombre
     *
     * @param string $hook Nombre del hook
     * @param string $type Tipo de hook ('action' o 'filter')
     * @return array
     */
    public function getHooksByName(string $hook, string $type = 'action'): array {
        $hooks = $type === 'filter' ? $this->filters : $this->actions;
        $found_hooks = [];
        
        foreach ($hooks as $registered_hook) {
            if ($registered_hook['hook'] === $hook) {
                $found_hooks[] = $registered_hook;
            }
        }
        
        return $found_hooks;
    }
} 