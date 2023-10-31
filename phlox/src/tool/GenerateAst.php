<?php 

class GenerateAst
{
    public function defineAst(string $outputDir, string $baseName, array $types)
    {
        $path = $outputDir . '/' . $baseName . '.php';
        $file = fopen($path,'w');
        fwrite($file,'<?php ');
        fwrite($file,'\n');
        fwrite($file,'namespace Phlox;');
        fwrite($file,'\n');
        fwrite($file, 'class '. $baseName . ' {');
        fwrite($file,'\n');
    }

    // public function defineType(resource $fileStream, array $types){
    //     fwrite($fileStream, ' trait Visitor')
    // }

}

if($argc != 1){
    echo 'Usage: generate_ast <output directory>';
    exit(-1);
}
