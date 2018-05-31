<?php

namespace JeffersonSimaoGoncalves\ApiPagination\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

/**
 * This is a simple component that injects pagination info into responses when
 * using CakePHP's PaginatorComponent alongside of CakePHP's JsonView or XmlView
 * classes.
 */
class ApiPaginationComponent extends Component
{
    /**
     *
     * @var string
     */
    public $model = "";

    /**
     * Default config.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'key' => 'pagination',
        'aliases' => [],
        'visible' => []
    ];

    /**
     * Holds the paging information array from the request.
     *
     * @var array
     */
    protected $pagingInfo = [];

    /**
     * Injects the pagination info into the response if the current request is a
     * JSON or XML request with pagination.
     *
     * @param \Cake\Event\Event $event The Controller.beforeRender event.
     *
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if (!$this->isPaginatedApiRequest()) {
            return;
        }

        $subject = $event->getSubject();

        if ($this->getController()->getRequest()->getParam('paging.' . $subject->name)) {
            $this->pagingInfo = $this->getController()->getRequest()->getParam('paging.' . $subject->name);
        } else {
            $this->pagingInfo = $this->getController()->getRequest()->getParam('paging.' . $this->model);
        }

        $config = $this->getConfig();

        if (!empty($config['aliases'])) {
            $this->setAliases();
        }

        if (!empty($config['visible'])) {
            $this->setVisibility();
        }

        $subject->set($config['key'], $this->pagingInfo);
        $subject->viewVars['_serialize'][] = $config['key'];
    }

    /**
     * Checks whether the current request is a JSON or XML request with
     * pagination.
     *
     * @return bool True if JSON or XML with paging, otherwise false.
     */
    protected function isPaginatedApiRequest()
    {
        return $this->getController()->getRequest()->getParam('paging') && $this->getController()->getRequest()->is(['json', 'xml']);
    }

    /**
     * Aliases the default pagination keys to the new keys that the user defines
     * in the config.
     *
     * @return void
     */
    protected function setAliases()
    {
        foreach ($this->getConfig('aliases') as $key => $value) {
            $this->pagingInfo[$value] = $this->pagingInfo[$key];
            unset($this->pagingInfo[$key]);
        }
    }

    /**
     * Removes any pagination keys that haven't been defined as visible in the
     * config.
     *
     * @return void
     */
    protected function setVisibility()
    {
        $visible = $this->getConfig('visible');
        foreach ($this->pagingInfo as $key => $value) {
            if (!in_array($key, $visible)) {
                unset($this->pagingInfo[$key]);
            }
        }
    }
}
