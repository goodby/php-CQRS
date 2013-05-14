<?php

namespace Goodby\CQRS\FictionalDomain\Domain\Model;

interface UserRepository
{
    /**
     * @return UserId
     */
    public function nextIdentity();

    /**
     * @param UserId $userId
     * @return User
     */
    public function userOfId(UserId $userId);

    /**
     * @param User $user
     * @return void
     */
    public function save(User $user);
}
