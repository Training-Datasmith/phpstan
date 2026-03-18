<?php

declare(strict_types=1);

namespace AttributeProblems;

#[\Attribute]
class Foo
{
}

#[Foo]
class Bar
{
}
