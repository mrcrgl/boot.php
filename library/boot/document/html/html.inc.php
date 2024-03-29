<?php
/**
 *
 *
 * @author marc
 *
 */
class BDocumentHtml extends BDocument
{

    var $_template = 'index.htpl';

    /**
   * Class constructor
   *
   * @param   array  $options  Associative array of options
   *
   * @since   2.0
   */
  public function __construct()
   {
    parent::__construct();

    // Set document type
    $this->_type = 'html';

    // Set default mime type and document metadata (meta data syncs with mime type by default)
    $this->setMimeEncoding('text/html');
  }

    /**
     * Set the document template
     *
     * @param   string  $template  Template file of document is to set to
     *
     * @return  BDocument instance of $this to allow chaining
     *
     * @since   2.0
     */
    public function setTemplate($template)
     {
        $this->_template = $template;

        return $this;
    }

    /**
     * Returns the document template file
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTemplate()
     {
        return $this->_template;
    }

    /**
     *
     *
     */
    public function assignDocumentVars()
    {
        $oRenderer =& $this->getRenderer();
        $oSession  =& BFactory::getSession();

        $oRenderer->assign('_document', &$this);

        $oLogin =& $oSession->get('login');
      if (is_object($oLogin) && $oLogin->loggedIn()) {
          $oRenderer->assign('_user', &$oLogin->obj);
      }
    }

    /**
     * Outputs the document
     *
     * @return  The rendered data
     *
     * @since   2.0
     */
    public function render()
     {
        if ($mdate = $this->getModifiedDate()) {
            BResponse::setHeader('Last-Modified', $mdate /* gmdate('D, d M Y H:i:s', time() + 900) . ' GMT' */);
        }

        BResponse::setHeader('Content-Type', $this->_mime . ($this->_charset ? '; charset=' . $this->_charset : ''));

        $oRenderer =& $this->getRenderer();

        if (!is_object($oRenderer)) {
            throw new Exception("No renderer instanciated");
        }

        $this->assignDocumentVars();

        $data = $oRenderer->fetch( $this->getTemplate() );
        $this->setBody($data);
    }
}