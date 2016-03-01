
class FileUnique {
        var $_filename;
        function __construct($filename)
        {
         $this->_filename = $filename;
        }        
        function __toString() {
            return $this->_filename ;
        }
}
class FileSequence {
        var $_head ;
        var $_tail ;
        var $_number ;
        var $_min ;
        var $_max ;
        function __construct($head,$tail,$number)
        {
            $this->_head=$head;
            $this->_tail=$tail;
            $this->_number=$number;
            $this->_min = $number[0];
            $this->_max = $number[count($number) -1];
        }
        function __toString() 
        {
            return $this->_head . "[".$this->_min ."-". $this->_max."]".$this->_tail ;
        }
    }

class FileSequenceFactory {

    var $_notsequence = array();
    var $_sequence = array();

    function addNotSequence( $filename ) {
        $this->_notsequence[] = $filename;
    }

    function addSequence( $head,$number,$tail ) {
        if ( array_key_exists( $tail ,  $this->_sequence ) == false )
        {
                $this->_sequence [$tail] = array();
        }

        if ( array_key_exists( $head ,  $this->_sequence[$tail] ) == false )
        {
             $this->_sequence[$tail][$head]   = array();
        }
        $this->_sequence[$tail][$head][]   = $number;
    }
    function getFiles()
    {
        $result= [];
        foreach( $this->_notsequence as  $filename ) 
        {
            $result[] = new FileUnique( $filename );
        }
        foreach( $this->_sequence as $tail=>$head ) 
        {
            foreach( $head as $h=>$numbers )
            $result[] = new FileSequence($h,$tail,$numbers);
        }
        return $result;
    }
}
[...]


class FileManager extends Ftp 
{
    public function downloadTemp($path) 
    {
        $localPath = tempnam(sys_get_temp_dir(), 'fmanager_');
        if ($this->download($path, $localPath)) {
            return $localPath;
        }
    }

    public function connect(  ) {
            return true;
    }
    public function listFilesRaw( $path )
    {
        $filelist = glob($path."/*");
        $fsf = new  FileSequenceFactory();
        $arraytailheadnumber = array();
        foreach( $filelist as $f)
        {
            preg_match_all('/([0-9a-zA-Z._\-]{1})([0-9]+)([a-zA-Z._\-]{1})/', $f, $out );
            if ( count( $out[2] ) > 0 ) 
            {
                 $tmpstr = explode($out[2][count($out[2])-1], $f );
                 $fsf->addSequence($tmpstr[0],$out[2][count($out[2])-1],$tmpstr[1]);
            } else {
                $fsf->addNotSequence($f);
            }
        }
        $result = array();
        foreach($fsf->getFiles() as $f) {
            if ( is_dir($f) ) {
                    $result[] = array( "name"=> basename((string)$f),"type"=>"dir");
             
             } else {
                    $result[] = array( "name"=> basename((string)$f),"type"=>"file");
             }

        }
        return $result;
    }