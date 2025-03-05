<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

class NestedWithParents extends ParentD {}

class ParentA
{
    public function a(self $self = new self()): self
    {
        return $self;
    }
}

class ParentB extends ParentA
{
    public function b(self $self = new self()): self
    {
        return $self;
    }

    public function ba(parent $parent = new parent()): parent
    {
        return $parent;
    }
}

class ParentC extends ParentB
{
    public function c(self $self = new self()): self
    {
        return $self;
    }

    public function cb(parent $parent = new parent()): parent
    {
        return $parent;
    }
}

class ParentD extends ParentC
{
    public function d(self $self = new self()): self
    {
        return $self;
    }

    public function dc(parent $parent = new parent()): parent
    {
        return $parent;
    }
}
