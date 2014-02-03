<?php

class Wcx_Filter_StripContent
{
    /**
     * Parse a block of PHP code and remove the contents of methods and functions,
     * leaving just their declarations and docs
     * @param  string $contents
     * @return string The striped code
     */
    public function stripContent($contents)
    {
        $curlyCount         =   0;
        $tokens             =   token_get_all($contents);
        $txt                =   '';
        $isClass            =   false;
        $classStarted       =   false;
        $isFunction         =   false;
        $functionStarted    =   false;
        $hasClass           =   false;
        $classConstant      =   false;
        $varOpen            =   false;
        $classEnded         =   false;

        foreach ($tokens as $i => $token) {
            if (is_string($token)) {
                if ($token == '{') {
                    if( $isClass && !$classStarted ){
                        $txt                .=  $token;
                        $classStarted       =   true;
                    } elseif( $isFunction && !$functionStarted ){
                        $txt                .=  $token;
                        $functionStarted    =   true;
                        $curlyCount++;
                    } else {
                        $curlyCount++;
                    }
                } else if ($token == '}') {
                    if( $varOpen ){
                        $varOpen    =   false;
                    } else {
                        $curlyCount--;
                        if( $curlyCount < 1 ){
                            if( $isFunction && $functionStarted ){
                                $txt                .=  $token;
                                $functionStarted    =   false;
                                $isFunction         =   false;
                            } else if( $isClass && $classStarted ){
                                $txt                .=  $token;
                                $classStarted       =   false;
                                $isClass            =   false;
                                $classEnded         =   true;
                            }
                        }
                    }
                } elseif (!$functionStarted && !$classEnded ) {
                    $txt .= $token;
                }
            } else {
                if( !$functionStarted && !$classEnded && in_array($token[0], $this->validTokens) ){
                    $txt .= $token[1];
                } elseif( !$this->flatten && in_array($token[0], $this->tagTokens) ){
                    $txt .= $token[1];
                }

                if ($token[0] == T_FUNCTION) {
                    $isFunction = true;
                } elseif($token[0] == T_CLASS) {
                    $isClass    = true;
                    $hasClass   = true;
                } elseif($token[0] == T_CURLY_OPEN) {
                    $varOpen    =   true;
                }
            }
        }

        return $txt;
    }
}
