<?php

namespace Galmi\Xacml;

/**
 * The <PolicySet> class is a top-level element in the XACML policy schema.
 * <PolicySet> is an aggregation of other policy sets and policies.
 *
 * @author Ildar Galiautdinov
 */
class PolicySet implements Evaluable
{

    protected $id;

    /**
     * The identifier of the policy-combining algorithm by which the
     * <PolicySet>, <CombinerParameters>, <PolicyCombinerParameters> and <PolicySetCombinerParameters>
     * components MUST be combined.
     *
     * @var string
     */
    protected $policyCombiningAlgId;
    /**
     * A free-form description of the policy set.
     *
     * @var string
     */
    protected $description;

    /**
     * The version number of the PolicySet.
     *
     * @var int
     */
    protected $version;

    /**
     * The <Target> class defines the applicability of a policy set to a set of decision requests.
     *
     * @var Target
     */
    protected $target;

    /**
     * A policy set that is included in this policy set.
     *
     * @var PolicySet[]
     */
    protected $policySets = array();

    /**
     * A policy that is included in this policy set.
     *
     * @var Policy[]
     */
    protected $policies = array();

    /**
     * Contains the set of <ObligationExpression> classes.
     *
     * @var
     */
    protected $obligationExpressions;

    /**
     * Contains the set of <AdviceExpression> classes.
     *
     * @var
     */
    protected $adviceExpressions;

    /**
     * @return CombiningAlgorithm\AlgorithmInterface
     * @throws Exception\FunctionNotFoundException
     */
    public function getPolicyCombiningAlgorithm()
    {
        /** @var CombiningAlgorithmRegistry $combiningAlgorithmFactory */
        $combiningAlgorithmFactory = Config::get(Config::COMBINING_ALGORITHM_REGISTRY);

        return $combiningAlgorithmFactory->get($this->policyCombiningAlgId);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get PolicySets
     *
     * @return PolicySet[]
     */
    public function getPolicySets()
    {
        return $this->policySets ?: $this->policySets = array();
    }

    /**
     * Add PolicySet
     *
     * @param PolicySet $policySet
     * @return $this
     */
    public function addPolicySet(PolicySet $policySet)
    {
        if (!in_array($policySet, $this->getPolicySets(), true)) {
            $this->policySets[] = $policySet;
        }

        return $this;
    }

    /**
     * Remove PolicySet
     *
     * @param PolicySet $policySet
     * @return $this|bool
     */
    public function removePolicySet(PolicySet $policySet)
    {
        if (in_array($policySet, $this->getPolicySets(), true)) {
            $key = array_search($policySet, $this->policySets, true);
            if ($key === false) {
                return false;
            }

            unset($this->policySets[$key]);
            $this->policySets = array_values($this->policySets);
        }

        return $this;
    }

    /**
     * Get Policy
     *
     * @return Policy[]
     */
    public function getPolicies()
    {
        return $this->policies ?: $this->policies = array();
    }

    /**
     * Add Policy
     *
     * @param Policy $policy
     * @return $this
     */
    public function addPolicy(Policy $policy)
    {
        if (!in_array($policy, $this->getPolicies(), true)) {
            $this->policies[] = $policy;
        }

        return $this;
    }

    /**
     * Remove Policy
     *
     * @param Policy $policy
     * @return $this|bool
     */
    public function removePolicy(Policy $policy)
    {
        if (in_array($policy, $this->getPolicies(), true)) {
            $key = array_search($policy, $this->policies, true);
            if ($key === false) {
                return false;
            }

            unset($this->policies[$key]);
            $this->policies = array_values($this->policies);
        }

        return $this;
    }


    /**
     * @return string
     */
    public function getPolicyCombiningAlgId()
    {
        return $this->policyCombiningAlgId;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Return array of PolicySets and Policies for Combining algorithm parameters
     *
     * @return array
     */
    protected function getPoliciesForCombingAlgorithm()
    {
        return array_merge($this->getPolicySets(), $this->getPolicies());
    }


    /**
     * @param string $policyCombiningAlgId
     * @return PolicySet
     */
    public function setPolicyCombiningAlgId($policyCombiningAlgId)
    {
        $this->policyCombiningAlgId = $policyCombiningAlgId;

        return $this;
    }

    /**
     * @param int $version
     * @return PolicySet
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param Target $target
     * @return PolicySet
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return Target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param PolicySet[] $policySets
     * @return PolicySet
     */
    public function setPolicySets($policySets)
    {
        $this->policySets = $policySets;

        return $this;
    }

    /**
     * @param Policy[] $policies
     * @return PolicySet
     */
    public function setPolicies($policies)
    {
        $this->policies = $policies;

        return $this;
    }

    /**
     *  -----------------------------------------------------------------------------
     * |    Target       | Rule values |              Policy Value                   |
     *  -----------------------------------------------------------------------------
     * | “Match”         | Don’t care  | Specified by the policy-combining algorithm |
     * | “No-match”      | Don’t care  | “NotApplicable”                             |
     * | “Indeterminate” | See Table 7 | See Table 7                                 |
     *  -----------------------------------------------------------------------------
     *
     * Table 7
     *  --------------------------------------------------------
     * | Combining algorithm Value | Policy set or policy Value |
     *  --------------------------------------------------------
     * | “NotApplicable”           | “NotApplicable”            |
     * | “Permit”                  | “Indeterminate{P}”         |
     * | “Deny”                    | “Indeterminate{D}”         |
     * | “Indeterminate”           | “Indeterminate{DP}”        |
     * | “Indeterminate{DP}”       | “Indeterminate{DP}”        |
     * | “Indeterminate{P}”        | “Indeterminate{P}”         |
     * | “Indeterminate{D}”        | “Indeterminate{D}”         |
     *  --------------------------------------------------------
     *
     * @inheritdoc
     */
    public function evaluate(Request $request)
    {
        $targetValue = null;
        $combiningAlgorithmDecision = null;
        $decision = Decision::NOT_APPLICABLE;
        $targetValue = $this->target->evaluate($request);
        if ($targetValue === Match::MATCH) {
            $decision = $this->getPolicyCombiningAlgorithm()
                ->evaluate($request, $this->getPoliciesForCombingAlgorithm());
        } elseif ($targetValue == Match::INDETERMINATE) {
            switch ($this->getPolicyCombiningAlgorithm()->evaluate($request, $this->getPoliciesForCombingAlgorithm())) {
                case (Decision::NOT_APPLICABLE):
                    $decision = Decision::NOT_APPLICABLE;
                    break;
                case (Decision::PERMIT):
                    $decision = Decision::INDETERMINATE_P;
                    break;
                case (Decision::DENY):
                    $decision = Decision::INDETERMINATE_D;
                    break;
                case (Decision::INDETERMINATE_D_P):
                    $decision = Decision::INDETERMINATE_D_P;
                    break;
                case (Decision::INDETERMINATE_P):
                    $decision = Decision::INDETERMINATE_P;
                    break;
                case (Decision::INDETERMINATE_D):
                    $decision = Decision::INDETERMINATE_D;
                    break;
                default:
                    $decision = Decision::INDETERMINATE_D_P;
            }
        }

        return $decision;
    }
}