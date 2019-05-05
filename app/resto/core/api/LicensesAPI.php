<?php
/*
 * Copyright 2018 Jérôme Gasperi
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
 * Licenses API
 */
class LicensesAPI
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
     *
     * Process licenses
     *
     * @SWG\Get(
     *      tags={"license"},
     *      path="/licenses/{licenseId}",
     *      summary="Get license description",
     *      description="Returns license(s) description(s)",
     *      operationId="getLicenses",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="licenseId",
     *          in="path",
     *          description="License identifier",
     *          required=false,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="License(s) description(s)"
     *      )
     * )
     *
     * @param array $params
     */
    public function getLicenses($params)
    {
        if (isset($params['licenseId'])) {
            return (new LicensesFunctions($this->context->dbDriver))->getLicense(array(
                'licenseId' => $params['licenseId']
            ));
        }
        return (new LicensesFunctions($this->context->dbDriver))->getLicenses();
    }

    /**
     * Sign license
     *
     *
     *  @SWG\Post(
     *      tags={"license"},
     *      path="/api/licenses/{licenseId}/sign",
     *      summary="Sign license",
     *      description="Sign license {licenseId}",
     *      operationId="signLicense",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="licenseId",
     *          in="path",
     *          description="License identifier",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Acknowledgment that the license was signed"
     *      ),
     *      @SWG\Response(
     *          response="404",
     *          description="License not found"
     *      ),
     *      @SWG\Response(
     *          response="403",
     *          description="Forbidden"
     *      )
     * )
     *
     * @param array $params
     * @param array $body
     */
    public function signLicense($params, $body)
    {
        if ($this->user->profile['email'] === 'unregistered') {
            RestoLogUtil::httpError(403);
        }
        
        return (new RestoLicense($this->context, $params['licenseId']))->load()->sign($this->user);
    }
}
