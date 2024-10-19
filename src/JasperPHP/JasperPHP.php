<?php
namespace Lopezsoft\JasperPHP\JasperPHP;

class JasperPHP
{
    protected string $executable = "/../JasperStarter/bin/jasperstarter";
    protected $the_command;
    protected $redirect_output;
    protected $background;
    protected $windows = false;
    protected $formats = ['pdf', 'rtf', 'xls', 'xlsx', 'docx', 'odt', 'ods', 'pptx', 'csv', 'html', 'xhtml', 'xml', 'jrprint'];
    protected $resource_directory; // Path to report resource dir or jar file

    /**
     * @throws \Exception
     */
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

    /**
     * @throws \Exception
     */
    public function compile($input_file, $output_file = false, $background = true, $redirect_output = true): JasperPHP
    {
        try {
            if(is_null($input_file) || empty($input_file))
                throw new \Exception("No input file", 1);

            $command = __DIR__ . $this->executable;

            $command .= " compile ";

            $command .= $input_file;

            if( $output_file !== false )
                $command .= " -o " . $output_file;

            $this->redirect_output  = $redirect_output;
            $this->background       = $background;
            $this->the_command      = escapeshellcmd($command);

            return $this;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws \Exception
     */
    public function process($input_file, $output_file = false, $format = ["pdf"], $parameters = [],
                            $db_connection = [], $background = true, $redirect_output = true): JasperPHP
    {
        try {
            if (empty($input_file)) {
                throw new \Exception('No input file', 1);
            }

            foreach ($format as $key) {
                if (!in_array($key, $this->formats)) {
                    throw new \Exception('Invalid format!', 1);
                }
            }

            $command = escapeshellcmd(__DIR__ . $this->executable) . ' process ' . escapeshellarg($input_file);

            if ($output_file !== false) {
                $command .= ' -o ' . escapeshellarg($output_file);
            }

            $format_string = implode(' ', $format);
            $command .= ' -f ' . escapeshellcmd($format_string);

            $command .= ' -r ' . escapeshellarg($this->resource_directory);

            if (count($parameters) > 0) {
                $command .= ' -P';
                foreach ($parameters as $key => $value) {
                    $command .= ' ' . $key . '=' . escapeshellarg($value);
                }
            }

            if (!empty($db_connection)) {
                $db_command = ' -t ' . $db_connection['driver'];

                $db_command .= !empty($db_connection['username']) ? ' -u ' . escapeshellarg($db_connection['username']) : '';
                $db_command .= !empty($db_connection['password']) ? ' -p ' . escapeshellarg($db_connection['password']) : '';
                $db_command .= !empty($db_connection['host']) ? ' -H ' . escapeshellarg($db_connection['host']) : '';
                $db_command .= !empty($db_connection['database']) ? ' -n ' . escapeshellarg($db_connection['database']) : '';
                $db_command .= !empty($db_connection['port']) ? ' --db-port ' . $db_connection['port'] : '';
                // Añade más opciones de conexión si es necesario

                $command .= $db_command;
            }

            $this->redirect_output  = $redirect_output;
            $this->background       = $background;
            $this->the_command      = $command;

            return $this;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * @throws \Exception
     */
    public function list_parameters($input_file): JasperPHP
    {
        try {
            if(is_null($input_file) || empty($input_file))
                throw new \Exception("No input file", 1);

            $command = __DIR__ . $this->executable;

            $command .= " list_parameters ";

            $command .= $input_file;

            $this->the_command = escapeshellcmd($command);

            return $this;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function output(): string
    {
        return escapeshellcmd($this->the_command);
    }

    /**
     * @throws \Exception
     */
    public function execute($run_as_user = false): array
    {
        if (!$this->windows) {
            if ($this->redirect_output) {
                $this->the_command .= " 2>&1";
            }

            if ($this->background) {
                $this->the_command .= " &";
            }

            if ($run_as_user !== false && strlen($run_as_user) > 0) {
                $this->the_command = "su -c \"{$this->the_command}\" {$run_as_user}";
            }
        }
        $output = [];
        $return_var = 0;
        exec($this->the_command, $output, $return_var);

        if ($return_var != 0) {
            $error_message = implode("\n", $output) ?:"Your report has an error and couldn't be processed! Try to output the command using the function `output();` and run it manually in the console.";
            throw new \Exception($error_message, 1);
        }
        return $output;
    }
}
