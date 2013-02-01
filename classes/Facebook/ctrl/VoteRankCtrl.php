<?php

class Facebook_VoteRankCtrl extends App_DbTableCtrl
{
    public function getClassName()
    {
        return 'Facebook_Vote_Rank';
    }
    public function recalcAction()
    {
        Facebook_Vote_Rank::Table()->recalc();
    }   
}
