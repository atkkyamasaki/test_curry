<?php

/**
 * ErrorHandler
 */
class ErrorHandler extends ErrorHandlerStandard
{
    public function memberInaccessible()
    {
        $dir = PathManager::getViewTemplateDirectory();
        $ext = NameManager::getTemplateExtension();
        $exists = file_exists(sprintf('%s/error/error.%s', $dir, $ext));
        if ($this->view instanceof ViewAbstract && $exists === true) {
            $this->view->setBasicVars();
            $this->view->setLayout('error');
            $this->view->setTemplate('not_found', 'error');
            $this->view->setOutputEncoding('UTF-8');
            $responseHeaders = array(
                'Content-Type: text/html; charset=UTF-8',
            );
            $response = new Response();
            $response->addHeaders($responseHeaders);
            $response->send();
            $this->view->render();
        } else {
            echo '404 Not Found';
        }
    }
}