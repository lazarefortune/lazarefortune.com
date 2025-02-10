<?php

namespace App\Infrastructure\Search;

interface IndexerInterface
{
    /**
     * Index data
     *
     * @param array $data
     * @return void
     */
    public function index( array $data ) : void;

    /**
     * Remove data from index
     *
     * @param string $id
     * @return void
     */
    public function remove( string $id ) : void;

    /**
     * Clean index
     *
     * @return void
     */
    public function clean() : void;
}