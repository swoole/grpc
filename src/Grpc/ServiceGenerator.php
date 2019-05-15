<?php
namespace Grpc;

use Gary\Protobuf\Compiler\CodeGeneratorInterface;
use Gary\Protobuf\Generator\CodeStringBuffer;
use Gary\Protobuf\Generator\CommentStringBuffer;
use Gary\Protobuf\Generator\PhpMsgGenerator;
use Gary\Protobuf\Internal\FieldDescriptor;
use Gary\Protobuf\Internal\FileDescriptor;
use Gary\Protobuf\Internal\MethodDescriptor;
use Gary\Protobuf\Internal\ServiceDescriptor;
use Google\Protobuf\Internal\CodeGeneratorRequest;
use Google\Protobuf\Internal\CodeGeneratorResponse;
use Google\Protobuf\Internal\CodeGeneratorResponse_File;

class ServiceGenerator implements CodeGeneratorInterface
{
    const CLASS_NAME_SEPARATOR = '_';
    const PHP_NAMESPACE_SEPARATOR = '\\';
    const PB_NAMESPACE_SEPARATOR = '.';
    const TAB = '    ';
    const EOL = PHP_EOL;

    /**
     * @var CodeGeneratorResponse_File[]
     */
    private $responseFiles = [];
    private $customArguments;

    private function generateClass($shortName, $namespace, $content)
    {
        $file = new CodeGeneratorResponse_File();
        $file->setName($this->getClassFilename($shortName, $namespace));
        $file->setContent('<?php' . PHP_EOL . $content);
        $this->responseFiles[] = $file;
    }

    /**
     * @param CodeGeneratorRequest $request
     * @param FileDescriptor[]     $fileDescriptors
     *
     * @return CodeGeneratorResponse
     */
    public function generate(CodeGeneratorRequest $request, $fileDescriptors): CodeGeneratorResponse
    {
        $this->customArguments = array();
        $parameter = $request->getParameter();
        if ($parameter) {
            parse_str($parameter, $this->customArguments);
        }
        $filesToGenerate = iterator_to_array($request->getFileToGenerate());
        foreach ($fileDescriptors as $i => $fileDescriptor) {
            if (!in_array($fileDescriptor->getName(), $filesToGenerate)) {
                continue;
            }
            $this->_generateFiles($fileDescriptor);
        }
        $response = new CodeGeneratorResponse();
        $response->setFile($this->responseFiles);
        return $response;
    }

    private function getClassFilename($className, $namespaceName)
    {
        return $className . '.php';
    }

    /**
     * @param FileDescriptor $file
     */
    private function _generateFiles($file)
    {
        foreach ($file->getService() as $service) {
            $this->_createInterface($service, $file);
            $this->_createService($service, $file);
            $this->_createClientStub($service, $file);
        }
    }

    /**
     * @param CommentStringBuffer $comment
     * @param FileDescriptor      $file
     * @param object              $descriptor
     * @param bool                $appendCodeInfo
     */
    private function appendCommentFromSourceCode(CommentStringBuffer $comment,
                                                 FileDescriptor $file, $descriptor, $appendCodeInfo = true)
    {
        $location = $file->getSourceCodeLocation($descriptor);
        if (!$location) {
            throw new \RuntimeException("cannot get location, path: " . var_export($descriptor->getSourceCodePath(), true));
        }
        if ($location->getLeadingComments()) {
            $comment->append($location->getLeadingComments());
            if ($appendCodeInfo) {
                $comment->newline();
            }
        }
        if ($location->getTrailingComments()) {
            $comment->append($location->getTrailingComments());
            if ($appendCodeInfo) {
                $comment->newline();
            }
        }
        if ($appendCodeInfo) {
            if ($descriptor instanceof FieldDescriptor) {
                $code = sprintf("%s %s = %s", $descriptor->getProtoTypeName(), $descriptor->getName(), $descriptor->getNumber());
                $comment->append(sprintf("Generated from protobuf <code>$code</code>"));
            }
        }
    }

    /**
     * @param $service
     * @param $file
     */
    private function _createInterface(ServiceDescriptor $service, FileDescriptor $file)
    {
        $buffer = new CodeStringBuffer(self::TAB);
        $fullName = $service->getClass();
        list($shortName, $namespace) = $this->parseFullClassName($fullName);
        $shortName .= "Interface";
        $comment = new CommentStringBuffer(self::TAB);
        $comment->alignWithBuffer($buffer);
        $this->appendCommentFromSourceCode($comment, $file, $service, false);
        /**
         * namespace
         */
        $buffer->newline()
            ->append("namespace App\\services;")->newline()
            ->append($comment)
            ->append("interface $shortName")
            ->append('{')
            ->incrIndentation()->newline();
        foreach ($service->getMethods() as $method) {
            $this->_createRpcMethods($buffer, $service, $file, $method, false);
        }
        $buffer->decrIndentation();
        $buffer->newline();
        $buffer->append('}');
        $this->generateClass($shortName, $namespace, $buffer->__toString());
    }

    /**
     * @param $service
     * @param $file
     */
    private function _createService(ServiceDescriptor $service, FileDescriptor $file)
    {
        $buffer = new CodeStringBuffer(self::TAB);
        $fullName = $service->getClass();
        list($shortName, $namespace) = $this->parseFullClassName($fullName);
        $serviceName = $shortName . "Service";
        $interfaceName = $shortName . 'Interface';
        $comment = new CommentStringBuffer(self::TAB);
        $comment->alignWithBuffer($buffer);
        $this->appendCommentFromSourceCode($comment, $file, $service, false);
        /**
         * namespace
         */
        $buffer->newline()
            ->append("namespace App\\services;")->newline()
            ->append($comment)
            ->append("class $serviceName extends \\SwFwLess\\services\\GrpcUnaryService implements \\App\services\\$interfaceName")
            ->append('{')
            ->incrIndentation()->newline();
        foreach ($service->getMethods() as $method) {
            $this->_createRpcMethods($buffer, $service, $file, $method);
        }
        $buffer->decrIndentation();
        $buffer->newline();
        $buffer->append('}');
        $this->generateClass($serviceName, $namespace, $buffer->__toString());
    }

    /**
     * @param ServiceDescriptor $service
     * @param FileDescriptor $file
     */
    private function _createClientStub(ServiceDescriptor $service, FileDescriptor $file)
    {
        $buffer = new CodeStringBuffer(self::TAB);
        $fullName = $service->getClass();
        list($shortName, $namespace) = $this->parseFullClassName($fullName);
        $clientName = $shortName . "Client";
        $comment = new CommentStringBuffer(self::TAB);
        $comment->alignWithBuffer($buffer);
        $this->appendCommentFromSourceCode($comment, $file, $service, false);
        $comment->newline();
        $comment->append("@mixin \\{$namespace}\\{$shortName}Client");
        /**
         * namespace
         */
        $buffer->newline()
            ->append("namespace App\\services;")->newline()
            ->append($comment)
            ->append("class $clientName  extends \\Grpc\\ClientStub")
            ->append('{')
            ->incrIndentation()
            ->newline()
            ->append('use \\SwFwLess\\components\\traits\\Singleton;')
            ->newline()
            ->append('protected $grpc_client = ' . "\\{$namespace}\\{$shortName}Client" . '::class;')
            ->decrIndentation()
            ->newline()
            ->append('}');

        $this->generateClass($clientName, $namespace, $buffer->__toString());
    }

    /**
     * @param $fullName
     *
     * @return array [classname, namespace]
     */
    private function parseFullClassName($fullName)
    {
        $parts = explode("\\", $fullName);
        $name = array_pop($parts);
        $namespace = implode("\\", $parts);
        return [$name, $namespace];
    }

    private function _createRpcMethods(CodeStringBuffer $buffer,
                                       ServiceDescriptor $service,
                                       FileDescriptor $file,
                                       MethodDescriptor $method,
                                       $implements = true)
    {
        $comment = new CommentStringBuffer(self::TAB);
        $comment->alignWithBuffer($buffer);
        $this->appendCommentFromSourceCode($comment, $file, $method, false);
        $requestClass = $method->getInputType()->getClass();
        $responseClass = $method->getOutputType()->getClass();
        $comment->append("@param \\$requestClass \$request ")
            ->append("@return \\$responseClass");
        $buffer->append($comment);
        $methodName = implode('', array_map('ucfirst', explode("_", $method->getName())));
        if ($implements) {
            $buffer->append("public function $methodName(\\$requestClass \$request)")
                ->append("{")
                ->incrIndentation()
                ->append("//todo implements interface")
                ->decrIndentation()
                ->append('}');
        } else {
            $buffer->append("public function $methodName(\\$requestClass \$request);");
        }
    }
}
