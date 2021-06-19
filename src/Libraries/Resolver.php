<?php namespace Gabriel\Libraries;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Class responsible for dynamically providing classes.
 *   - Inspired from Laravel app()->make() class provider
 *
 * @author  Gabriel Lucernas Pascual <me@ghabxph.info>
 * @since   2018.07.23
 */
class Resolver
{
    /**
     * Singleton instance of this class
     *
     * @var Resolver
     */
    private static $oInstance;

    /**
     * Binded classes
     *
     * @var array
     */
    private static $aBoundClasses;

    /**
     * Array of classes whose dependencies are specified manually
     *
     * @var array
     */
    private static $aClassDependencies;

    /**
     * Classes that are bound to a singleton instance
     * @var array
     */
    private static $aSingletons;

    /**
     * Current class to reflect
     *
     * @var string
     */
    private $sCurrentClass;

    /**
     * Class's Reflection
     *
     * @var ReflectionClass
     */
    private $oClassReflection;

    /**
     * Class's dependencies
     *
     * @var array
     */
    private $aDependencies;

    /**
     * Implements a concrete implementation of a certain class
     *
     * @param string $sClass
     * @param string $sConcrete
     */
    public static function bind($sClass, $sConcrete)
    {
        self::$aBoundClasses[$sClass] = $sConcrete;
    }

    /**
     * Binds class to a singleton
     *
     * @param string $sClass
     * @param $oSingleton
     */
    public static function singleton($sClass, $oSingleton)
    {
        self::$aSingletons[$sClass] = $oSingleton;
    }

    /**
     * Sets class dependencies specifically
     *
     * @param string $sClass
     * @param array  $aDependencies
     */
    public static function setClassDependencies($sClass, $aDependencies)
    {
        self::$aClassDependencies[$sClass] = $aDependencies;
    }

    /**
     * Returns the singleton instance of this class
     *
     * @return Resolver
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = new Resolver();
        }
        return self::$oInstance;
    }

    /**
     * Provides the desired class
     *
     * @param string $sClass
     * @return mixed
     * @throws ReflectionException
     */
    public static function provide($sClass)
    {
        $oInstance = self::getInstance();
        return $oInstance->doProvide($sClass);
    }

    /**
     * Provides the desired class
     *
     * @param string $sClass
     * @return mixed
     * @throws ReflectionException
     */
    public function doProvide($sClass)
    {
        $this->setCurrentClass($sClass);
        if (isset(self::$aSingletons[$sClass]) === true) {
            return self::$aSingletons[$sClass];
        }
        $this->getClassReflection()
            ->getClassDependencies();
        return new $sClass(...$this->aDependencies);
    }

    /**
     * Seeks final class implementation through bound classes, then sets the current final class
     *
     * @param string $sClass
     * @return $this
     */
    private function setCurrentClass($sClass)
    {
        $this->sCurrentClass = (isset(self::$aBoundClasses[$sClass]) === false) ? $sClass : self::$aBoundClasses[$sClass];
        if ($sClass !== $this->sCurrentClass) {
            return $this->setCurrentClass($this->sCurrentClass);
        }
        return $this;
    }

    /**
     * Provides the dependencies of the specified class
     *
     * @return Resolver
     * @throws ReflectionException
     */
    private function getClassDependencies()
    {
        $aParameters = $this->oClassReflection->getConstructor() === null ? [] : $this->oClassReflection->getConstructor()->getParameters();
        $this->aDependencies = (isset(self::$aClassDependencies[$this->sCurrentClass]) === true) ? self::$aClassDependencies[$this->sCurrentClass] : [];
        foreach ($aParameters as $iKey => $oParameter) {
            $this->aDependencies[$iKey] = $this->provideIfParameterNotYetExist($iKey, $oParameter);
        }
        return $this;
    }

    /**
     * Provide parameter if parameter is not yet set by default
     *
     * @param  int                  $iKey
     * @param  ReflectionParameter  $oParameter
     * @return mixed
     * @throws ReflectionException
     */
    private function provideIfParameterNotYetExist($iKey, ReflectionParameter $oParameter)
    {
        if (isset(self::$aClassDependencies[$this->sCurrentClass]) && isset(self::$aClassDependencies[$this->sCurrentClass][$iKey])) {
            return (new Resolver())->doProvide(self::$aClassDependencies[$this->sCurrentClass][$iKey]);
        }
        return ($oParameter->getClass() === null) ? $oParameter->getDefaultValue() : (new Resolver())->doProvide($oParameter->getClass()->name);
    }

    /**
     * Retrieves current class's reflection
     *
     * @return Resolver
     * @throws ReflectionException
     */
    private function getClassReflection()
    {
        $this->oClassReflection = new ReflectionClass($this->sCurrentClass);
        return $this;
    }
}
