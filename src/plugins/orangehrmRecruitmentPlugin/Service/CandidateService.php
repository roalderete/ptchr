<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace OrangeHRM\Recruitment\Service;

use Exception;
use OrangeHRM\Core\Service\EmailService;
use OrangeHRM\Config\Config;
use OrangeHRM\Entity\WorkflowStateMachine;
use OrangeHRM\Recruitment\Dao\CandidateDao;
use OrangeHRM\Entity\Candidate; 
use OrangeHRM\Entity\CandidateVacancy;
use OrangeHRM\Entity\Interview;
use OrangeHRM\Entity\InterviewAttachment;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class CandidateService
{
    public const RECRUITMENT_CANDIDATE_VACANCY_REMOVED = 15;
    public const RECRUITMENT_CANDIDATE_ACTION_ADD = 16;
    public const RECRUITMENT_CANDIDATE_ACTION_APPLIED = 17;

    public const STATUS_MAP = [
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_ATTACH_VACANCY => 'APPLICATION INITIATED',
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_SHORTLIST => 'SHORTLISTED',
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_REJECT => 'REJECTED',
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_SHEDULE_INTERVIEW => 'INTERVIEW SCHEDULED',
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_MARK_INTERVIEW_PASSED => 'INTERVIEW PASSED',
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_MARK_INTERVIEW_FAILED => 'INTERVIEW FAILED',
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_OFFER_JOB => 'JOB OFFERED',
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_DECLINE_OFFER => 'OFFER DECLINED',
        WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_HIRE => 'HIRED',
    ];

    public const OTHER_ACTIONS_MAP = [
        self::RECRUITMENT_CANDIDATE_ACTION_ADD => 'ADDED',
        self::RECRUITMENT_CANDIDATE_VACANCY_REMOVED => 'REMOVED',
        self::RECRUITMENT_CANDIDATE_ACTION_APPLIED => 'APPLIED'
    ];

    protected ?CandidateDao $candidateDao = null;
    protected ?EmailService $emailService = null;

    /**
     * @return EmailService
     */
    public function getEmailService(): EmailService
    {
        if (!$this->emailService instanceof EmailService) {
            $this->emailService = new EmailService(); 
        }
        return $this->emailService;
    }

    /**
     * Get Candidate Dao
     * @return CandidateDao
     */
    public function getCandidateDao(): CandidateDao
    {
        if (is_null($this->candidateDao)) {
            $this->candidateDao = new CandidateDao();
        }
        return $this->candidateDao;
    }
    
    /**
     * Constructor.
     * @param CandidateDao|null $candidateDao
     */
    public function __construct(CandidateDao $candidateDao = null) 
    {
        $this->candidateDao = $candidateDao;
    }

    /**
     * Finds the latest InterviewAttachment entity for a given Interview ID.
     * NOTE: This method relies on the DAO capability added in CandidateDao.
     * @param int $interviewId
     * @return InterviewAttachment|null
     */
    protected function getLatestInterviewAttachment(int $interviewId): ?InterviewAttachment
    {
        // Call the new method we added to the DAO
        $attachment = $this->getCandidateDao()->findLatestInterviewAttachmentByInterviewId($interviewId); 
        
        if ($attachment instanceof InterviewAttachment) {
            return $attachment;
        }
        
        error_log("Could not find latest InterviewAttachment for Interview ID: {$interviewId}");
        return null;
    }

    protected function getLatestCandidateHistoryNote(Candidate $candidate, int $action): ?string
    {
    return $this->getCandidateDao()->findLatestHistoryNoteByCandidateAndAction($candidate->getId(), $action);
    }

    /**
     * Executes a candidate action and handles notifications.
     * This function should be called after the candidate status is successfully updated.
     * @param Candidate $candidate The Candidate Entity object
     * @param int $action The WorkflowStateMachine constant (e.g., REJECT, OFFER_JOB)
     * @param array $params Optional parameters (ignored for interview/offer actions).
     * @return Candidate
     */
    public function triggerNotificationForAction(Candidate $candidate, int $action, array $params = []): Candidate
    {
        try {
            $jobTitle = $this->getJobTitleFromCandidate($candidate);
            $attachmentEntity = null;
            
            switch ($action) {
                case WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_REJECT:
                    $this->sendCandidateNotification(
                        $candidate, 
                        'Update Regarding Your Application for ' . $jobTitle, 
                        'reject.txt', 
                        []
                    );
                    break;
                    
                case WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_SHEDULE_INTERVIEW:
                    $latestInterview = $this->getLatestCandidateInterview($candidate);
                    if (!$latestInterview instanceof Interview) {
                        error_log("Skipping Interview Email: Could not find latest Interview entity for Candidate ID {$candidate->getId()}. Check DAO property name.");
                        return $candidate; 
                    }
                        
                    $this->sendCandidateNotification(
                        $candidate, 
                        'Interview Invitation for ' . $jobTitle, 
                        'interview_schedule.txt', 
                        [
                            'interviewName' => $latestInterview->getInterviewName(),
                            'interviewDate' => $latestInterview->getInterviewDate()->format('Y-m-d'),
                            'interviewTime' => $latestInterview->getInterviewTime()->format('H:i'),
                            'interviewDetails' => $latestInterview->getNote() ?? 'For more instructions, send an email to this address, and we will address your concern promptly.',
                        ]
                    );
                    break;

                    case WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_MARK_INTERVIEW_PASSED:
                        $latestInterview = $this->getLatestCandidateInterview($candidate);
                        if (!$latestInterview instanceof Interview) {
                        error_log("Skipping Interview Email: Could not find latest Interview entity for Candidate ID {$candidate->getId()}. Check DAO property name.");
                        return $candidate; 
                    }

                    $this->sendCandidateNotification(
                        $candidate, 
                        'Interview Status Update: ' . $jobTitle, 
                        'interview_passed.txt',
                        [
                            'interviewName' => $latestInterview->getInterviewName(),
                            'interviewDate' => $latestInterview->getInterviewDate()->format('Y-m-d'),
                            'interviewTime' => $latestInterview->getInterviewTime()->format('H:i'),
                        ]
                    );
                    break;

                    case WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_MARK_INTERVIEW_FAILED:
                        $latestInterview = $this->getLatestCandidateInterview($candidate);
                        if (!$latestInterview instanceof Interview) {
                        error_log("Skipping Interview Email: Could not find latest Interview entity for Candidate ID {$candidate->getId()}. Check DAO property name.");
                        return $candidate; 
                    }

                    $this->sendCandidateNotification(
                        $candidate, 
                        'Interview Status Update: ' . $jobTitle, 
                        'interview_failed.txt',
                        [
                            'interviewName' => $latestInterview->getInterviewName(),
                            'interviewDate' => $latestInterview->getInterviewDate()->format('Y-m-d'),
                            'interviewTime' => $latestInterview->getInterviewTime()->format('H:i'),
                        ]
                    );
                    break;

                    case WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_OFFER_JOB:
                        $latestInterview = $this->getLatestCandidateInterview($candidate);
                        if ($latestInterview instanceof Interview) {
                        $attachmentEntity = $this->getLatestInterviewAttachment($latestInterview->getId());
                    }
                        $offerNote = $this->getLatestCandidateHistoryNote($candidate, $action);

                        $this->sendCandidateNotification(
                        $candidate, 
                        'Formal Offer for ' . $jobTitle . ' Position',
                        'job_offer.txt', 
                        [
                            'offerNote' => $offerNote ?? '',
                        ],
                        $attachmentEntity
                    );
                    break;

                    case WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_DECLINE_OFFER:
                        $this->sendCandidateNotification(
                        $candidate, 
                        'Offer Decline Acknowledged for ' . $jobTitle, 
                        'offer_declined.txt', 
                        []
                    );
                    break;

                    case WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_HIRE:
                        $offerNote = $this->getLatestCandidateHistoryNote($candidate, $action);

                        $this->sendCandidateNotification(
                        $candidate, 
                        'Welcome to the Team! Your Employment Confirmation for ' . $jobTitle, 
                        'hired.txt', 
                        [
                            'offerNote' => $offerNote ?? '',
                        ],
                    );
                    break;
            }
        } catch (TransportExceptionInterface $e) {
            error_log("Recruitment Email Transport Error for Candidate ID {$candidate->getId()}: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Recruitment Email Logic Error for Candidate ID {$candidate->getId()}: " . $e->getMessage());
        }
        return $candidate; 
    }

    /**
     * Finds the latest Interview entity for the given Candidate.
     * This assumes the latest interview is the one most recently scheduled.
     * @param Candidate $candidate
     * @return Interview|null
     */
    public function getLatestCandidateInterview(Candidate $candidate): ?Interview
    {
        // FIX: Replaced direct collection access with a DAO method call
        $candidateVacancy = $this->getCandidateDao()->getCandidateVacancyByCandidateId($candidate->getId());
        
        if ($candidateVacancy instanceof CandidateVacancy) {
            $candidateVacancyId = $candidateVacancy->getId();
            
            // Find the latest Interview by candidateVacancyId using the DAO
            // NOTE: This relies on findLatestInterviewByCandidateVacancyId being added to CandidateDao.php
            $latestInterview = $this->getCandidateDao()->findLatestInterviewByCandidateVacancyId($candidateVacancyId);
            
            if ($latestInterview instanceof Interview) {
                return $latestInterview;
            }
        }
        return null;
    }
    
    /**
     * Handles retrieving the job title via the CandidateVacancy relationship.
     * @param Candidate $candidate
     * @return string
     */
    protected function getJobTitleFromCandidate(Candidate $candidate): string
    {
        $jobTitle = 'A Vacancy'; 
        // FIX: Replaced direct collection access with a DAO method call for fetching the active CandidateVacancy
        $candidateVacancy = $this->getCandidateDao()->getCandidateVacancyByCandidateId($candidate->getId());

        if ($candidateVacancy instanceof CandidateVacancy) {
            $vacancy = $candidateVacancy->getVacancy();
            if ($vacancy && method_exists($vacancy, 'getName')) {
                $jobTitle = $vacancy->getName(); 
            }
        }
        return $jobTitle;
    }

    /**
     * Generates the email body from a template file.
     * @param string $templateFile The name of the template file (e.g., 'reject.txt')
     * @param array $placeholders Array of placeholder keys
     * @param array $replacements Array of replacement values
     * @return string
     * @throws Exception If the template file cannot be found.
     */
    public function generateEmailBody(string $templateFile, array $placeholders, array $replacements): string
    {
        $templatePath = Config::get(Config::PLUGINS_DIR) . 
                         '/orangehrmRecruitmentPlugin/config/data/' . 
                         $templateFile;

        if (!file_exists($templatePath)) {
            throw new Exception("Recruitment Email Template not found at: " . $templatePath);
        }

        $body = file_get_contents($templatePath);
        $body = str_replace($placeholders, $replacements, $body);

        return nl2br($body);
    }

    /**
     * Generic method to send a notification to a candidate.
     * @param Candidate $candidate
     * @param string $subject
     * @param string $templateFile
     * @param array $templateData
     * @param InterviewAttachment|null $attachmentEntity
     * @return bool
     * @throws TransportExceptionInterface
     */
    protected function sendCandidateNotification(
        Candidate $candidate, 
        string $subject, 
        string $templateFile, 
        array $templateData,
        ?InterviewAttachment $attachmentEntity = null
    ): bool
    {
        $candidateEmail = $candidate->getEmail(); 
        $firstName = $candidate->getFirstName();
        $middleName = $candidate->getMiddleName();
        $lastName = $candidate->getLastName();
        $candidateName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
        $jobTitle = $this->getJobTitleFromCandidate($candidate);

        if (empty($candidateEmail)) {
            error_log("Skipping email: Candidate {$candidateName} has no email address.");
            return false;
        }

        $placeholders = ['{{ firstName }}', '{{ middleName }}', '{{ lastName }}', '{{ candidateName }}', '{{ jobTitle }}'];
        $replacements = [
            $firstName,
            $middleName, 
            $lastName, 
            $candidateName, 
            $jobTitle
        ];
        
        foreach ($templateData as $key => $value) {
            $placeholders[] = "{{ {$key} }}"; 
            $replacements[] = $value;
        }

        $body = $this->generateEmailBody($templateFile, $placeholders, $replacements);
        
        $this->getEmailService()->setMessageTo([$candidateEmail]);
        
        $this->getEmailService()->setMessageFrom(
            [$this->getEmailService()->getEmailConfig()->getSentAs() => 'PTC-HR Recruitment Team']
        );
        
        $this->getEmailService()->setMessageSubject($subject);
        $this->getEmailService()->setMessageBody($body);

        if ($attachmentEntity instanceof InterviewAttachment) {
            
            $fileContentResource = $attachmentEntity->getFileContent();
            $attachmentName = $attachmentEntity->getFileName();
            $attachmentType = $attachmentEntity->getFileType(); 
            $fileContent = null;

            if (is_resource($fileContentResource)) {
                $fileContent = stream_get_contents($fileContentResource);
            } else {
                $fileContent = (string) $fileContentResource;
            }

            if (!empty($fileContent)) {
                try {
                    $this->getEmailService()->addRawAttachment(
                        $fileContent,
                        $attachmentName, 
                        $attachmentType
                    ); 
                    
                } catch (Exception $e) {
                    error_log("Failed to attach file (from BLOB) {$attachmentName} to email: " . $e->getMessage());
                }
            } else {
                error_log("Attachment entity found, but file content (BLOB) was empty for {$attachmentName}.");
            }
    }
        
        return $this->getEmailService()->sendEmail();
    }
}