<?php

function getDockerVarsFileName()
{
    global $global;
    if(empty($global['docker_vars'])){
        return false;
    }
    return $global['docker_vars'];
}

function getDockerVars()
{
    global $_getDockerVars;
    if (!isset($_getDockerVars)) {
        if (file_exists(getDockerVarsFileName())) {
            $content = file_get_contents(getDockerVarsFileName());
            $_getDockerVars = json_decode($content);
        } else {
            $_getDockerVars = false;
        }
    }
    return $_getDockerVars;
}

function isDocker()
{
    return !empty(getDockerVars());
}

function getDockerInternalURL()
{
    return "http://live:8080/";
}

function getDockerStatsURL()
{
    return getDockerInternalURL() . "stat";
}
