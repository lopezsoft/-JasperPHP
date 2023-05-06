<?php
namespace JasperPHP;

class JasperPHP
{
    protected $executable = "/../JasperStarter/bin/jasperstarter";
    protected $the_command;
    protected $redirect_output;
    protected $background;
    protected $windows = false;
    protected $formats = array('pdf', 'rtf', 'xls', 'xlsx', 'docx', 'odt', 'ods', 'pptx', 'csv', 'html', 'xhtml', 'xml', 'jrprint');
    protected $resource_directory; // Path to report resource dir or jar file

    function __construct($resource_dir = false)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
           $this->windows = true;

        if (!$resource_dir) {
            $this->resource_directory = __DIR__ . "/../../../../../";
        } else {
            if (!file_exists($resource_dir))
                throw new \Exception("Invalid resource directory", 1);

            $this->resource_directory = $resource_dir;
        }
    }

    public static function __callStatic($method, $parameters)
    {
        // Create a new instance of the called class, in this case it is Post
        $model = get_called_class();

        // Call the requested method on the newly created object
        return call_user_func_array(array(new $model, $method), $parameters);
    }

    public function compile($input_file, $output_file = false, $background = true, $redirect_output = true): JasperPHP
    {
        try {
            if(is_null($input_file) || empty($input_file))
                throw new Exception("No input file", 1);

            $command = __DIR__ . $this->executable;

            $command .= " compile ";

            $command .= $input_file;

            if( $output_file !== false )
                $command .= " -o " . $output_file;

            $this->redirect_output  = $redirect_output;
            $this->background       = $background;
            $this->the_command      = escapeshellcmd($command);

            return $this;
        }catch (Exception $e){
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function process($input_file, $output_file = false, $format = ["pdf"], $parameters = [],
                            $db_connection = [], $background = true, $redirect_output = true): JasperPHP
    {
        try {
            if (is_null($input_file) || empty($input_file)) {
                throw new Exception("No input file", 1);
            }

            $format = is_array($format) ? $format : [$format];

            $invalid_formats = array_diff($format, $this->formats);
            if (!empty($invalid_formats)) {
                throw new Exception("Invalid format: " . implode(", ", $invalid_formats), 1);
            }

            $command = escapeshellcmd(__DIR__ . $this->executable) . " process $input_file";

            if ($output_file !== false) {
                $command .= " -o $output_file";
            }

            $command .= " -f " . implode(" ", $format);

            $command .= " -r $this->resource_directory";

            if (!empty($parameters)) {
                $command .= " -P";
                foreach ($parameters as $key => $value) {
                    $command .= is_string($value) ? " {$key}='{$value}'" : " $key=$value";
                }
            }

            $flag_bits = 0;
            if ($redirect_output) {
                $flag_bits |= 1;
            }
            if ($background) {
                $flag_bits |= 2;
            }
            $command .= " -d $flag_bits";

            if (!empty($db_connection)) {
                $command .= " -t $db_connection[driver]";

                $command .= " -u " . ($db_connection['username'] ?? '');
                $command .= " -p " . ($db_connection['password'] ?? '');
                $command .= " -H " . ($db_connection['host'] ?? '');
                $command .= " -n " . ($db_connection['database'] ?? '');
                $command .= " --db-port " . ($db_connection['port'] ?? '');
                $command .= " --db-driver " . ($db_connection['jdbc_driver'] ?? '');
                $command .= " --db-url " . ($db_connection['jdbc_url'] ?? '');
                $command .= ' --jdbc-dir ' . ($db_connection['jdbc_dir'] ?? '');
                $command .= ' --db-sid ' . ($db_connection['db_sid'] ?? '');
                $command .= ' --json-query ' . ($db_connection['json_query'] ?? '');
                $command .= ' --data-file ' . ($db_connection['data_file'] ?? '');
            }

            $this->redirect_output = $redirect_output;
            $this->background = $background;
            $this->the_command = $command;

            return $this;
        }catch (Exception $e){
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function list_parameters($input_file): JasperPHP
    {
        try {
            if(is_null($input_file) || empty($input_file))
                throw new Exception("No input file", 1);

            $command = __DIR__ . $this->executable;

            $command .= " list_parameters ";

            $command .= $input_file;

            $this->the_command = escapeshellcmd($command);

            return $this;
        }catch (Exception $e){
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function output(): string
    {
        return escapeshellcmd($this->the_command);
    }

    public function execute($run_as_user = false): array
    {
        try {
            $command = $this->the_command;
            if ($this->redirect_output && !$this->windows) {
                $command .= " 2>&1";
            }
            if ($this->background && !$this->windows) {
                $command .= " &";
            }
            if ($run_as_user && !$this->windows) {
                $command = sprintf("su -c \"%s\" %s", $command, $run_as_user);
            }
            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);
            if ($return_var != 0) {
                $error_msg = $output[0] ?? "Your report has an error and couldn't be processed! Try to output the command using the function `output();` and run it manually in the console.";
                throw new Exception($error_msg, 1);
            }
            return $output;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
