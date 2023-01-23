<?php
/*
 * Copyright 2023 Jérôme Gasperi
 *
 * Licensed under the Apache License, version 2.0 (the "License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * Status API
 */
class StatusAPI
{
    private $context;
    private $user;

    /**
     * Constructor
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
    }

    /**
     * Liveness
     * 
     *    @OA\Get(
     *      path="/_isLive",
     *      summary="Liveness status",
     *      description="Returns HTTP 200 only if the service is live i.e. it cannot yet process requests.",
     *      tags={"Status"},
     *      @OA\Response(
     *          response="200",
     *          description="Service is live",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Service is live"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="503",
     *          description="Not available"
     *      )
     *    )
     */
    public function isLive()
    {
        return RestoLogUtil::success('Service is live');
    }

}
